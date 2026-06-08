<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\CorretorMetaMensal;
use App\Models\DocumentoObrigatorioPreCadastro;
use App\Models\Indicacao;
use App\Models\Operadora;
use App\Models\PreCadastro;
use App\Models\Proposta;
use App\Models\RequisitoDocumental;
use App\Models\Tarefa;
use App\Models\TimelineEvento;
use App\Models\TipoDocumento;
use App\Models\User;
use App\Models\Vida;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WilsonDashboardDemoSeeder extends Seeder
{
    private const MARCADOR = '[dashboard_demo]';

    public function run(): void
    {
        $wilson = User::query()
            ->where('email', 'wilsonluiz@icloud.com')
            ->orWhere('name', 'like', '%WILSON%')
            ->firstOrFail();

        Model::unguarded(function () use ($wilson) {
            DB::transaction(function () use ($wilson) {
                $this->limparDemo($wilson);

                $operadoras = $this->operadoras();
                $tiposDocumentos = $this->tiposDocumentos();

                $this->criarMetaMensal($wilson);
                $this->criarLeadsEmAberto($wilson);
                $this->criarPropostasEnviadas($wilson, $operadoras);
                $this->criarPreCadastros($wilson, $operadoras, $tiposDocumentos);
                $this->criarImplantacoes($wilson, $operadoras);
                $this->criarContratosFechados($wilson, $operadoras);
                $this->criarAlertasETarefas($wilson);
            });
        });
    }

    private function limparDemo(User $wilson): void
    {
        Alerta::query()
            ->where('user_id', $wilson->id)
            ->where('chave', 'like', 'dashboard_demo_%')
            ->delete();

        Tarefa::query()
            ->where('user_id', $wilson->id)
            ->where('descricao', 'like', '%'.self::MARCADOR.'%')
            ->delete();

        Indicacao::query()
            ->where('user_id', $wilson->id)
            ->where('observacoes', 'like', '%'.self::MARCADOR.'%')
            ->get()
            ->each
            ->delete();

        CorretorMetaMensal::query()
            ->where('user_id', $wilson->id)
            ->whereDate('mes_referencia', now()->startOfMonth()->toDateString())
            ->delete();
    }

    private function operadoras(): array
    {
        return collect(['SulAmérica', 'Bradesco Saúde', 'Amil', 'Unimed', 'Porto Saúde', 'Alice'])
            ->mapWithKeys(fn (string $nome) => [
                $nome => Operadora::query()->updateOrCreate(['nome' => $nome], ['ativa' => true]),
            ])
            ->all();
    }

    private function tiposDocumentos(): array
    {
        return collect([
            'Documento de identidade com foto',
            'CPF',
            'Comprovante de Residência',
            'Carta de Permanência',
            'Cartão CNPJ',
            'Contrato Social',
            'Relação de Vidas',
        ])->mapWithKeys(function (string $nome) {
            return [
                $nome => TipoDocumento::query()->firstOrCreate(
                    ['nome' => $nome],
                    ['slug' => Str::slug($nome), 'ativo' => true]
                ),
            ];
        })->all();
    }

    private function criarMetaMensal(User $wilson): void
    {
        CorretorMetaMensal::query()->create([
            'user_id' => $wilson->id,
            'mes_referencia' => now()->startOfMonth()->toDateString(),
            'meta_comissao' => 12000,
            'comissao_realizada' => 8450,
        ]);
    }

    private function criarLeadsEmAberto(User $wilson): void
    {
        $leads = [
            ['nome' => 'Marina Albuquerque', 'vidas' => 4, 'tipo' => 'familiar', 'valor' => 'R$ 2.500 a R$ 3.500', 'dias' => 3, 'status' => 'aguardando_contato'],
            ['nome' => 'Eduardo Nascimento', 'vidas' => 2, 'tipo' => 'individual', 'valor' => 'R$ 1.200 a R$ 1.800', 'dias' => 2, 'status' => 'em_contato'],
            ['nome' => 'Clínica Horizonte Ltda', 'vidas' => 12, 'tipo' => 'empresarial', 'valor' => 'R$ 8.000 a R$ 12.000', 'dias' => 1, 'status' => 'nova'],
            ['nome' => 'Patrícia Gomes', 'vidas' => 3, 'tipo' => 'familiar', 'valor' => 'R$ 2.000 a R$ 2.800', 'dias' => 8, 'status' => 'aguardando_contato'],
        ];

        foreach ($leads as $index => $lead) {
            $data = now()->subDays($lead['dias'])->setTime(9 + $index, 20);

            $indicacao = Indicacao::query()->create([
                'user_id' => $wilson->id,
                'origem' => 'indicacao_cliente',
                'nome_cliente' => $lead['nome'],
                'telefone' => '(11) 9'.rand(7000, 9999).'-'.rand(1000, 9999),
                'email' => Str::slug($lead['nome'], '.').'@cliente-demo.com.br',
                'tipo_plano' => $lead['tipo'],
                'quantidade_vidas' => $lead['vidas'],
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'possui_preferencias' => true,
                'operadoras_preferidas' => ['SulAmérica', 'Bradesco Saúde'],
                'hospitais_preferidos' => ['Sírio-Libanês', 'Oswaldo Cruz'],
                'faixa_valor_mensal' => $lead['valor'],
                'etapa' => 'lead',
                'status' => $lead['status'],
                'observacoes' => self::MARCADOR.' Lead realista para teste do dashboard.',
            ]);

            $indicacao->forceFill(['created_at' => $data, 'updated_at' => $data])->save();
            $this->timeline($indicacao, 'Lead recebido', 'Cliente entrou na base comercial para primeiro contato.', $data);
        }
    }

    private function criarPropostasEnviadas(User $wilson, array $operadoras): void
    {
        $registros = [
            ['nome' => 'Renata Martins', 'vidas' => 2, 'tipo' => 'familiar', 'operadora' => 'SulAmérica', 'valor' => 1890, 'comissao' => 226.80, 'dias' => 10],
            ['nome' => 'Rafael Costa', 'vidas' => 1, 'tipo' => 'individual', 'operadora' => 'Amil', 'valor' => 740, 'comissao' => 88.80, 'dias' => 9],
            ['nome' => 'Studio Bela Forma ME', 'vidas' => 8, 'tipo' => 'empresarial', 'operadora' => 'Unimed', 'valor' => 6120, 'comissao' => 734.40, 'dias' => 6],
            ['nome' => 'André Carvalho', 'vidas' => 3, 'tipo' => 'familiar', 'operadora' => 'Porto Saúde', 'valor' => 2560, 'comissao' => 307.20, 'dias' => 4],
        ];

        foreach ($registros as $index => $registro) {
            $data = now()->subDays($registro['dias'])->setTime(11 + $index, 10);
            $indicacao = $this->indicacaoBase($wilson, $registro['nome'], $registro['tipo'], $registro['vidas'], 'propostas', 'proposta_enviada', $data);
            $grupo = (string) Str::uuid();

            foreach ([1, 2] as $numero) {
                $path = $this->pdfDemo("proposta-{$indicacao->id}-{$numero}.pdf", $registro['nome'], $registro['operadora']);

                Proposta::query()->create([
                    'indicacao_id' => $indicacao->id,
                    'operadora_id' => $operadoras[$registro['operadora']]->id,
                    'titulo' => $numero === 1 ? 'Cotação principal' : 'Cotação comparativa',
                    'arquivo_pdf_path' => $path,
                    'public_token' => (string) Str::uuid(),
                    'public_group_token' => $grupo,
                    'validade' => now()->addDays(20 + $index),
                    'quantidade_vidas' => $registro['vidas'],
                    'valor_mensal' => $registro['valor'] + (($numero - 1) * 180),
                    'percentual_comissao' => 12,
                    'valor_comissao_prevista' => $registro['comissao'] + (($numero - 1) * 24),
                    'observacoes' => self::MARCADOR.' Proposta enviada automaticamente para demonstração.',
                    'status' => 'enviada',
                    'enviado_email_em' => $data,
                    'created_at' => $data,
                    'updated_at' => $data,
                ]);
            }

            $this->timeline($indicacao, 'Propostas enviadas', 'Cotações enviadas por e-mail para análise do cliente.', $data);
        }
    }

    private function criarPreCadastros(User $wilson, array $operadoras, array $tiposDocumentos): void
    {
        $registros = [
            ['nome' => 'Família Teixeira', 'vidas' => 4, 'tipo' => 'familiar', 'status' => 'documentacao_pendente', 'dias' => 4],
            ['nome' => 'Bianca Moreira', 'vidas' => 1, 'tipo' => 'individual', 'status' => 'aguardando_envio', 'dias' => 3],
            ['nome' => 'Grupo Prime Serviços', 'vidas' => 16, 'tipo' => 'empresarial', 'status' => 'documentacao_em_analise', 'dias' => 1],
            ['nome' => 'Marcelo e Ana Souza', 'vidas' => 2, 'tipo' => 'familiar', 'status' => 'correcao_solicitada', 'dias' => 5],
        ];

        foreach ($registros as $index => $registro) {
            $data = now()->subDays($registro['dias'])->setTime(10 + $index, 45);
            $indicacao = $this->indicacaoBase($wilson, $registro['nome'], $registro['tipo'], $registro['vidas'], 'pre_cadastros', $registro['status'], $data);

            $preCadastro = PreCadastro::query()->create([
                'indicacao_id' => $indicacao->id,
                'token' => Str::random(40),
                'chave_acesso' => strtoupper(Str::random(6)),
                'chave_expira_em' => now()->addDays(10),
                'tipo_proposta' => $registro['tipo'],
                'pessoa' => $registro['tipo'] === 'empresarial' ? 'PJ' : 'PF',
                'status' => $registro['status'],
                'formulario_bloqueado' => false,
                'motivos_correcao' => $registro['status'] === 'correcao_solicitada' ? 'Comprovante de residência precisa ser reenviado legível.' : null,
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            $titular = Vida::query()->create([
                'pre_cadastro_id' => $preCadastro->id,
                'tipo' => $registro['tipo'] === 'empresarial' ? 'socio' : 'titular',
                'ordem' => 1,
                'nome' => $registro['nome'],
                'sexo' => 'masculino',
                'data_nascimento' => now()->subYears(38)->subDays($index * 20),
                'cpf' => $this->cpfDemo($index),
                'telefone' => '(11) 99953-5578',
                'email' => Str::slug($registro['nome'], '.').'@cliente-demo.com.br',
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            $this->documentoObrigatorio($preCadastro, $titular, $tiposDocumentos['Documento de identidade com foto'], 'Documento de identidade com foto', 'pendente', $data);
            $this->documentoObrigatorio($preCadastro, $titular, $tiposDocumentos['CPF'], 'CPF', $registro['status'] === 'correcao_solicitada' ? 'corrigir' : 'pendente', $data);
            $this->documentoObrigatorio($preCadastro, $titular, $tiposDocumentos['Comprovante de Residência'], 'Comprovante de Residência', 'pendente', $data);

            if ($registro['tipo'] === 'familiar') {
                $dependente = Vida::query()->create([
                    'pre_cadastro_id' => $preCadastro->id,
                    'tipo' => 'dependente',
                    'vinculo_beneficiario_id' => $titular->id,
                    'ordem' => 2,
                    'nome' => $registro['nome'].' Dependente',
                    'parentesco' => 'Cônjuge',
                    'sexo' => 'feminino',
                    'data_nascimento' => now()->subYears(34),
                    'cpf' => $this->cpfDemo($index + 20),
                    'created_at' => $data,
                    'updated_at' => $data,
                ]);

                $this->documentoObrigatorio($preCadastro, $dependente, $tiposDocumentos['Documento de identidade com foto'], 'Documento de identidade com foto', 'pendente', $data);
            }

            $path = $this->pdfDemo("pre-cadastro-proposta-{$indicacao->id}.pdf", $registro['nome'], 'Bradesco Saúde');

            Proposta::query()->create([
                'indicacao_id' => $indicacao->id,
                'operadora_id' => $operadoras['Bradesco Saúde']->id,
                'titulo' => 'Proposta aceita para pré-cadastro',
                'arquivo_pdf_path' => $path,
                'public_token' => (string) Str::uuid(),
                'public_group_token' => (string) Str::uuid(),
                'validade' => now()->addDays(15),
                'quantidade_vidas' => $registro['vidas'],
                'valor_mensal' => $registro['vidas'] * 690,
                'percentual_comissao' => 12,
                'valor_comissao_prevista' => $registro['vidas'] * 82.80,
                'observacoes' => self::MARCADOR.' Proposta vinculada ao pré-cadastro.',
                'status' => 'aceita',
                'enviado_email_em' => $data,
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            $this->timeline($indicacao, 'Pré-cadastro gerado', 'Cliente recebeu link para preencher dados e documentos.', $data);
        }
    }

    private function criarImplantacoes(User $wilson, array $operadoras): void
    {
        $registros = [
            ['nome' => 'Condomínio Solar das Acácias', 'vidas' => 22, 'tipo' => 'empresarial', 'status' => 'contrato_em_analise', 'dias' => 6],
            ['nome' => 'Letícia Ramos', 'vidas' => 2, 'tipo' => 'familiar', 'status' => 'aguardando_vigencia', 'dias' => 2],
        ];

        foreach ($registros as $index => $registro) {
            $data = now()->subDays($registro['dias'])->setTime(15, 10 + $index);
            $indicacao = $this->indicacaoBase($wilson, $registro['nome'], $registro['tipo'], $registro['vidas'], 'implantacoes', $registro['status'], $data);

            DB::table('implantacoes')->insert([
                'indicacao_id' => $indicacao->id,
                'status' => $registro['status'],
                'data_inicio' => $data->toDateString(),
                'data_aprovacao' => null,
                'observacoes' => self::MARCADOR.' Implantação em andamento para teste operacional.',
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            $path = $this->pdfDemo("implantacao-proposta-{$indicacao->id}.pdf", $registro['nome'], 'Porto Saúde');

            Proposta::query()->create([
                'indicacao_id' => $indicacao->id,
                'operadora_id' => $operadoras['Porto Saúde']->id,
                'titulo' => 'Proposta aprovada em implantação',
                'arquivo_pdf_path' => $path,
                'public_token' => (string) Str::uuid(),
                'public_group_token' => (string) Str::uuid(),
                'validade' => now()->addDays(12),
                'quantidade_vidas' => $registro['vidas'],
                'valor_mensal' => $registro['vidas'] * 610,
                'percentual_comissao' => 12,
                'valor_comissao_prevista' => $registro['vidas'] * 73.20,
                'observacoes' => self::MARCADOR.' Proposta em implantação.',
                'status' => 'aceita',
                'enviado_email_em' => $data,
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            $this->timeline($indicacao, 'Implantação iniciada', 'Documentação aprovada e contrato em análise na operadora.', $data);
        }
    }

    private function criarContratosFechados(User $wilson, array $operadoras): void
    {
        $contratos = [
            ['nome' => 'Empresa Norte Digital', 'vidas' => 14, 'tipo' => 'empresarial', 'operadora' => 'SulAmérica', 'mensal' => 11800, 'comissao' => 1416, 'meses' => 0],
            ['nome' => 'Família Cardoso', 'vidas' => 4, 'tipo' => 'familiar', 'operadora' => 'Bradesco Saúde', 'mensal' => 3480, 'comissao' => 417.60, 'meses' => 0],
            ['nome' => 'Paulo Henrique Lima', 'vidas' => 1, 'tipo' => 'individual', 'operadora' => 'Amil', 'mensal' => 820, 'comissao' => 98.40, 'meses' => 0],
            ['nome' => 'Clínica Vitta Care', 'vidas' => 18, 'tipo' => 'empresarial', 'operadora' => 'Unimed', 'mensal' => 15300, 'comissao' => 1836, 'meses' => 0],
            ['nome' => 'Larissa e Bruno Pires', 'vidas' => 3, 'tipo' => 'familiar', 'operadora' => 'Porto Saúde', 'mensal' => 2670, 'comissao' => 320.40, 'meses' => 0],
            ['nome' => 'Agência Plano Azul', 'vidas' => 9, 'tipo' => 'empresarial', 'operadora' => 'Alice', 'mensal' => 7650, 'comissao' => 918, 'meses' => 1],
            ['nome' => 'Família Mendonça', 'vidas' => 5, 'tipo' => 'familiar', 'operadora' => 'SulAmérica', 'mensal' => 4300, 'comissao' => 516, 'meses' => 1],
            ['nome' => 'Carlos Alberto Freitas', 'vidas' => 1, 'tipo' => 'individual', 'operadora' => 'Amil', 'mensal' => 690, 'comissao' => 82.80, 'meses' => 2],
            ['nome' => 'Mercado São Jorge', 'vidas' => 11, 'tipo' => 'empresarial', 'operadora' => 'Bradesco Saúde', 'mensal' => 9240, 'comissao' => 1108.80, 'meses' => 3],
            ['nome' => 'Família Azevedo', 'vidas' => 3, 'tipo' => 'familiar', 'operadora' => 'Unimed', 'mensal' => 2510, 'comissao' => 301.20, 'meses' => 4],
            ['nome' => 'Oficina Rodasul', 'vidas' => 7, 'tipo' => 'empresarial', 'operadora' => 'Porto Saúde', 'mensal' => 5890, 'comissao' => 706.80, 'meses' => 5],
            ['nome' => 'Fernanda Bittencourt', 'vidas' => 1, 'tipo' => 'individual', 'operadora' => 'Alice', 'mensal' => 760, 'comissao' => 91.20, 'meses' => 6],
        ];

        foreach ($contratos as $index => $contratoDemo) {
            $data = now()->subMonthsNoOverflow($contratoDemo['meses'])->setDay(min(24, 4 + $index))->setTime(14, 30);
            $indicacao = $this->indicacaoBase($wilson, $contratoDemo['nome'], $contratoDemo['tipo'], $contratoDemo['vidas'], 'carteira', 'convertido_em_cliente', $data);

            $cliente = Cliente::query()->create([
                'indicacao_id' => $indicacao->id,
                'user_id' => $wilson->id,
                'nome' => $contratoDemo['nome'],
                'email' => Str::slug($contratoDemo['nome'], '.').'@cliente-demo.com.br',
                'telefone' => '(11) 9'.rand(8000, 9999).'-'.rand(1000, 9999),
                'inicio_vigencia' => $data->copy()->addDays(10),
                'valor_mensal' => $contratoDemo['mensal'],
                'status' => 'ativo',
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            $path = $this->pdfDemo("contrato-proposta-{$indicacao->id}.pdf", $contratoDemo['nome'], $contratoDemo['operadora']);

            $proposta = Proposta::query()->create([
                'indicacao_id' => $indicacao->id,
                'operadora_id' => $operadoras[$contratoDemo['operadora']]->id,
                'cliente_id' => $cliente->id,
                'titulo' => 'Proposta contratada',
                'arquivo_pdf_path' => $path,
                'public_token' => (string) Str::uuid(),
                'public_group_token' => (string) Str::uuid(),
                'validade' => $data->copy()->addDays(30),
                'quantidade_vidas' => $contratoDemo['vidas'],
                'valor_mensal' => $contratoDemo['mensal'],
                'percentual_comissao' => 12,
                'valor_comissao_prevista' => $contratoDemo['comissao'],
                'observacoes' => self::MARCADOR.' Proposta convertida em contrato.',
                'status' => 'aceita',
                'enviado_email_em' => $data,
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            Contrato::query()->create([
                'usuario_id' => $wilson->id,
                'cliente_id' => $cliente->id,
                'proposta_id' => $proposta->id,
                'operadora_id' => $operadoras[$contratoDemo['operadora']]->id,
                'tipo_contrato' => $contratoDemo['tipo'],
                'status' => 'ativo',
                'quantidade_vidas' => $contratoDemo['vidas'],
                'valor_mensal' => $contratoDemo['mensal'],
                'percentual_comissao' => 12,
                'valor_comissao_prevista' => $contratoDemo['comissao'],
                'valor_comissao_real' => $contratoDemo['comissao'],
                'numero_contrato' => 'NXO-'.now()->format('Y').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'iniciado_em' => $data->copy()->addDays(10),
                'renovacao_em' => $data->copy()->addYear(),
                'reajuste_em' => $data->copy()->addYear(),
                'observacoes' => self::MARCADOR.' Contrato ativo para teste de dashboard e carteira.',
                'created_at' => $data,
                'updated_at' => $data,
            ]);

            $this->timeline($indicacao, 'Contrato fechado', 'Contrato vigente com comissão prevista e realizada registrada.', $data);
        }
    }

    private function criarAlertasETarefas(User $wilson): void
    {
        $indicacoes = Indicacao::query()
            ->where('user_id', $wilson->id)
            ->where('observacoes', 'like', '%'.self::MARCADOR.'%')
            ->latest()
            ->take(6)
            ->get();

        foreach ($indicacoes as $index => $indicacao) {
            Tarefa::query()->create([
                'user_id' => $wilson->id,
                'indicacao_id' => $indicacao->id,
                'tipo' => 'follow_up',
                'titulo' => 'Retornar contato: '.$indicacao->nome_cliente,
                'descricao' => self::MARCADOR.' Tarefa operacional para validar agenda, alertas e produtividade.',
                'vencimento' => now()->addDays($index + 1),
                'status' => $index % 3 === 0 ? 'atrasada' : 'pendente',
                'created_at' => now()->subDays($index),
                'updated_at' => now()->subDays($index),
            ]);

            Alerta::query()->create([
                'user_id' => $wilson->id,
                'indicacao_id' => $indicacao->id,
                'chave' => 'dashboard_demo_'.$indicacao->id,
                'data_referencia' => now()->addDays($index + 1),
                'titulo' => 'Acompanhamento pendente',
                'mensagem' => 'Existe uma ação comercial aguardando retorno para '.$indicacao->nome_cliente.'.',
                'tipo' => 'atencao',
                'status' => 'pendente',
                'lido' => false,
                'created_at' => now()->subHours($index + 2),
                'updated_at' => now()->subHours($index + 2),
            ]);
        }
    }

    private function indicacaoBase(User $wilson, string $nome, string $tipo, int $vidas, string $etapa, string $status, Carbon $data): Indicacao
    {
        $indicacao = Indicacao::query()->create([
            'user_id' => $wilson->id,
            'origem' => 'dashboard_demo',
            'nome_cliente' => $nome,
            'telefone' => '(11) 9'.rand(7000, 9999).'-'.rand(1000, 9999),
            'email' => Str::slug($nome, '.').'@cliente-demo.com.br',
            'tipo_plano' => $tipo,
            'quantidade_vidas' => $vidas,
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'possui_preferencias' => true,
            'operadoras_preferidas' => ['SulAmérica', 'Bradesco Saúde', 'Amil'],
            'hospitais_preferidos' => ['Hospital São Luiz', 'Oswaldo Cruz'],
            'faixa_valor_mensal' => $vidas > 5 ? 'R$ 5.000 a R$ 15.000' : 'R$ 800 a R$ 4.000',
            'etapa' => $etapa,
            'status' => $status,
            'observacoes' => self::MARCADOR.' Registro criado para teste real da V1.',
        ]);

        $indicacao->forceFill(['created_at' => $data, 'updated_at' => $data])->save();

        return $indicacao;
    }

    private function documentoObrigatorio(
        PreCadastro $preCadastro,
        Vida $vida,
        TipoDocumento $tipoDocumento,
        string $titulo,
        string $status,
        Carbon $data
    ): void {
        DocumentoObrigatorioPreCadastro::query()->create([
            'pre_cadastro_id' => $preCadastro->id,
            'vida_proposta_id' => $vida->id,
            'tipo_documento_id' => $tipoDocumento->id,
            'requisito_documental_id' => RequisitoDocumental::query()
                ->where('tipo_documento_id', $tipoDocumento->id)
                ->value('id'),
            'titulo' => $titulo,
            'obrigatorio' => true,
            'ordem' => DocumentoObrigatorioPreCadastro::query()->where('pre_cadastro_id', $preCadastro->id)->count() + 1,
            'status' => $status,
            'observacoes' => self::MARCADOR.' Documento obrigatório para validar pendências reais.',
            'created_at' => $data,
            'updated_at' => $data,
        ]);
    }

    private function timeline(Indicacao $indicacao, string $titulo, string $descricao, Carbon $data): void
    {
        TimelineEvento::query()->create([
            'indicacao_id' => $indicacao->id,
            'titulo' => $titulo,
            'descricao' => self::MARCADOR.' '.$descricao,
            'created_at' => $data,
            'updated_at' => $data,
        ]);
    }

    private function pdfDemo(string $nomeArquivo, string $cliente, string $operadora): string
    {
        $path = 'propostas/dashboard-demo/'.$nomeArquivo;
        $conteudo = "%PDF-1.4\n"
            ."1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            ."2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            ."3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >> endobj\n"
            ."4 0 obj << /Length 112 >> stream\n"
            ."BT /F1 18 Tf 72 720 Td (Proposta Nexo Saude) Tj 0 -28 Td ({$cliente}) Tj 0 -28 Td ({$operadora}) Tj ET\n"
            ."endstream endobj\n"
            ."trailer << /Root 1 0 R >>\n%%EOF";

        Storage::disk('public')->put($path, $conteudo);

        return $path;
    }

    private function cpfDemo(int $offset): string
    {
        $cpfs = [
            '416.068.888-89',
            '201.456.320-70',
            '149.486.960-30',
            '070.987.720-03',
            '935.411.347-80',
            '541.622.050-10',
            '705.484.450-52',
            '873.624.170-00',
        ];

        return $cpfs[$offset % count($cpfs)];
    }
}
