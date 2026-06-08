<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NexoAiInsightService
{
    public function insightForUser(int $userId, array $metricas): array
    {
        if (! $this->temVolumeMinimo($metricas)) {
            return $this->semDadosSuficientes();
        }

        $hash = md5(json_encode($metricas['resumo'] ?? []));
        $cacheKey = "dashboard:ai-insight:v2:{$userId}:{$hash}";

        return Cache::remember($cacheKey, now()->addHours(3), function () use ($metricas) {
            if (! config('services.openai.api_key')) {
                return $this->fallback($metricas);
            }

            try {
                $response = Http::timeout((int) config('services.openai.timeout', 60))
                    ->withToken(config('services.openai.api_key'))
                    ->acceptJson()
                    ->post('https://api.openai.com/v1/responses', [
                        'model' => config('services.openai.model', 'gpt-4.1-mini'),
                        'input' => [[
                            'role' => 'user',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => $this->prompt($metricas),
                            ]],
                        ]],
                        'text' => [
                            'format' => [
                                'type' => 'json_schema',
                                'name' => 'dashboard_sales_manager_insight',
                                'strict' => true,
                                'schema' => $this->schema(),
                            ],
                        ],
                    ]);

                if (! $response->successful()) {
                    Log::warning('Falha ao gerar insight do dashboard', [
                        'status' => $response->status(),
                    ]);

                    return $this->fallback($metricas);
                }

                $text = data_get($response->json(), 'output.0.content.0.text');
                $data = is_string($text) ? json_decode($text, true) : null;

                if (! is_array($data)) {
                    return $this->fallback($metricas);
                }

                return [
                    'titulo' => trim((string) ($data['insight_principal'] ?? '')),
                    'descricao' => trim((string) ($data['diagnostico'] ?? '')),
                    'recomendacao' => trim((string) ($data['recomendacao'] ?? '')),
                    'fonte' => 'openai',
                ];
            } catch (\Throwable $exception) {
                Log::warning('Exceção segura ao gerar insight do dashboard', [
                    'message' => $exception->getMessage(),
                ]);

                return $this->fallback($metricas);
            }
        });
    }

    private function prompt(array $metricas): string
    {
        return 'Você é a Nexo IA atuando como gerente de vendas sênior para um corretor de planos de saúde. '.
            'Fale diretamente com o corretor, de forma objetiva, prática e firme. '.
            'Seu trabalho é identificar onde ele está perdendo vendas no funil e indicar a próxima ação comercial. '.
            'Não seja genérico, não use tom de relatório e não diga apenas que existe gargalo. '.
            'Explique o ponto de perda, o motivo provável com base nos dados disponíveis e o que fazer hoje. '.
            'Não invente números, causas, nomes de clientes ou tendências que não estejam nos dados. '.
            'Se houver pouca informação, diga isso e recomende a ação mínima para gerar dados. '.
            'Retorne JSON em português do Brasil. '.
            'Limites de tamanho: insight_principal com até 90 caracteres; diagnostico com até 180 caracteres; recomendacao com até 160 caracteres. '.
            'Dados agregados reais: '.json_encode([
                'funil' => $metricas['funil'] ?? [],
                'kpis' => collect($metricas['metricas'] ?? [])->map(fn ($item) => [
                    'titulo' => $item['titulo'] ?? '',
                    'valor' => $item['valor'] ?? '',
                    'variacao' => $item['variacao'] ?? 0,
                    'empty' => $item['empty'] ?? false,
                ])->values()->all(),
                'alertas' => collect($metricas['alertasDashboard'] ?? [])->map(fn ($item) => [
                    'titulo' => $item['titulo'] ?? '',
                    'total' => $item['total'] ?? 0,
                ])->values()->all(),
                'resumo' => $metricas['resumo'] ?? [],
            ], JSON_UNESCAPED_UNICODE).'.';
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'insight_principal' => ['type' => 'string'],
                'motivo' => ['type' => 'string'],
                'diagnostico' => ['type' => 'string'],
                'recomendacao' => ['type' => 'string'],
            ],
            'required' => ['insight_principal', 'motivo', 'diagnostico', 'recomendacao'],
        ];
    }

    private function temVolumeMinimo(array $metricas): bool
    {
        $resumo = $metricas['resumo'] ?? [];

        return ((int) ($resumo['total_indicacoes'] ?? 0)) >= 3
            || ((int) ($resumo['total_contratos'] ?? 0)) >= 1
            || ((float) ($resumo['comissao_prevista'] ?? 0)) > 0;
    }

    private function semDadosSuficientes(): array
    {
        return [
            'titulo' => 'Você ainda não tem volume suficiente para uma leitura confiável.',
            'descricao' => 'Ainda faltam leads, propostas ou contratos no período para identificar onde as vendas estão travando.',
            'recomendacao' => 'Cadastre novas oportunidades e mantenha cada lead avançando de etapa para a IA apontar o gargalo real.',
            'fonte' => 'fallback',
        ];
    }

    private function fallback(array $metricas): array
    {
        $funil = $metricas['funil'] ?? [];
        $alertas = collect($metricas['alertasDashboard'] ?? [])->sortByDesc('total')->first();
        $principalPerda = (string) ($funil['principal_perda'] ?? '');

        if (($alertas['total'] ?? 0) > 0) {
            return [
                'titulo' => $this->tituloParaAlerta($alertas['titulo'] ?? ''),
                'descricao' => $this->diagnosticoParaAlerta($alertas, $principalPerda),
                'recomendacao' => $this->recomendacaoParaAlerta($alertas['titulo'] ?? ''),
                'fonte' => 'fallback',
            ];
        }

        if (($funil['leads'] ?? 0) === 0) {
            return $this->semDadosSuficientes();
        }

        if ($principalPerda !== '') {
            return [
                'titulo' => 'Sua maior perda está em '.$principalPerda.'.',
                'descricao' => 'O funil mostra queda nessa transição. É aí que você deve concentrar follow-up e remoção de pendências.',
                'recomendacao' => 'Revise os clientes dessa etapa hoje, priorize os mais antigos e defina a próxima ação de cada um.',
                'fonte' => 'fallback',
            ];
        }

        return [
            'titulo' => 'Seu funil não mostra gargalo crítico agora.',
            'descricao' => 'Os dados do período indicam operação estável, sem uma perda concentrada em uma etapa específica.',
            'recomendacao' => 'Mantenha follow-up diário nas propostas abertas e avance pré-cadastros sem deixar documentação parada.',
            'fonte' => 'fallback',
        ];
    }

    private function tituloParaAlerta(string $titulo): string
    {
        return match (true) {
            str_contains($titulo, 'Pré-cadastro') => 'Você está perdendo vendas em pré-cadastros parados.',
            str_contains($titulo, 'Leads') => 'Seus leads estão esfriando por falta de contato.',
            str_contains($titulo, 'Cotações') => 'Você está deixando cotações sem retorno.',
            str_contains($titulo, 'Documentação') => 'A documentação está travando seus fechamentos.',
            default => 'Existe uma prioridade comercial para resolver hoje.',
        };
    }

    private function diagnosticoParaAlerta(array $alerta, string $principalPerda): string
    {
        $total = (int) ($alerta['total'] ?? 0);
        $descricao = (string) ($alerta['descricao'] ?? '');
        $perda = $principalPerda !== '' ? ' A maior perda do funil está em '.$principalPerda.'.' : '';

        return "Há {$descricao} exigindo ação. Isso reduz a chance de fechamento porque o cliente fica parado no momento de decisão.{$perda}";
    }

    private function recomendacaoParaAlerta(string $titulo): string
    {
        return match (true) {
            str_contains($titulo, 'Pré-cadastro') => 'Ligue para esses clientes hoje, destrave o envio de documentos e defina prazo claro para concluir.',
            str_contains($titulo, 'Leads') => 'Entre em contato com os leads mais antigos primeiro e registre a próxima etapa antes do fim do dia.',
            str_contains($titulo, 'Cotações') => 'Faça follow-up das cotações abertas, reforce diferença de preço/cobertura e peça uma decisão objetiva.',
            str_contains($titulo, 'Documentação') => 'Liste os documentos pendentes, peça reenvio com orientação clara e acompanhe até regularizar.',
            default => 'Abra os alertas, resolva os itens mais antigos e registre a próxima ação de cada oportunidade.',
        };
    }
}
