<?php

namespace App\Services;

use App\Models\AvaliacaoAtendimento;
use App\Models\Indicacao;
use App\Models\User;

class WhatsAppLinkService
{
    public const DEFAULT_LEAD_TEMPLATE = 'Olá, {nome}! Tudo bem? Sou seu corretor da Nexo Saúde. Recebi seu interesse em um plano {tipo_plano} para {quantidade_vidas} vida(s) e gostaria de te ajudar com as melhores opções.';

    public const DEFAULT_CONTRACT_TEMPLATE = "Olá, {nome}! Tudo bem? Temos uma notícia excelente: seu contrato entrou em vigência em {data_vigencia}. 🎉\n\nAgora seu plano de saúde já está ativo. Se precisar de qualquer orientação sobre utilização, carteirinha, rede credenciada ou próximos passos, estou à disposição.\n\nPara mim é muito importante saber como foi sua experiência nesse atendimento. Você pode responder uma avaliação rápida por este link?\n{link_avaliacao}\n\nObrigado pela confiança!";

    public function sanitizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        return str_starts_with($digits, '55') ? $digits : '55'.$digits;
    }

    public function buildClientLink(?string $phone): string
    {
        $phone = $this->sanitizePhone((string) $phone);

        return $phone !== '' ? 'https://wa.me/'.$phone : '#';
    }

    public function buildLeadLink(Indicacao $lead, User $corretor): string
    {
        $phone = $this->sanitizePhone((string) $lead->telefone);

        if ($phone === '') {
            return '#';
        }

        $template = $corretor->corretorPerfil?->mensagem_primeiro_contato_whatsapp
            ?: self::DEFAULT_LEAD_TEMPLATE;

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->parseMessageTemplate($template, $lead));
    }

    public function buildContractReviewLink(AvaliacaoAtendimento $avaliacao, User $corretor, string $linkAvaliacao): string
    {
        $telefone = $this->sanitizePhone((string) $avaliacao->cliente?->telefone);

        if ($telefone === '') {
            return '#';
        }

        $template = $corretor->corretorPerfil?->mensagem_contrato_vigente_whatsapp
            ?: self::DEFAULT_CONTRACT_TEMPLATE;

        $mensagem = $this->parseContractTemplate($template, $avaliacao, $linkAvaliacao);

        return 'https://wa.me/'.$telefone.'?text='.rawurlencode($mensagem);
    }

    public function parseMessageTemplate(string $template, Indicacao $lead): string
    {
        return strtr($template, [
            '{nome}' => (string) $lead->nome_cliente,
            '{telefone}' => (string) $lead->telefone,
            '{tipo_plano}' => (string) $lead->tipo_plano,
            '{quantidade_vidas}' => (string) $lead->quantidade_vidas,
            '{cidade}' => (string) $lead->cidade,
            '{estado}' => (string) $lead->estado,
        ]);
    }

    public function parseContractTemplate(string $template, AvaliacaoAtendimento $avaliacao, string $linkAvaliacao): string
    {
        $cliente = $avaliacao->cliente;
        $contrato = $cliente?->contratos?->sortByDesc('created_at')->first();

        return strtr($template, [
            '{nome}' => (string) $cliente?->nome,
            '{telefone}' => (string) $cliente?->telefone,
            '{email}' => (string) $cliente?->email,
            '{data_vigencia}' => $cliente?->inicio_vigencia?->format('d/m/Y') ?: $contrato?->iniciado_em?->format('d/m/Y') ?: 'hoje',
            '{tipo_plano}' => (string) ($contrato?->tipo_contrato ?: $avaliacao->indicacao?->tipo_plano),
            '{quantidade_vidas}' => (string) ($contrato?->quantidade_vidas ?: $avaliacao->indicacao?->quantidade_vidas),
            '{link_avaliacao}' => $linkAvaliacao,
        ]);
    }
}
