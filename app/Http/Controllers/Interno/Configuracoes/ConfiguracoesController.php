<?php

namespace App\Http\Controllers\Interno\Configuracoes;

use App\Http\Controllers\Controller;
use App\Models\CorretorPerfil;
use App\Models\Indicacao;
use App\Rules\CpfCnpjValido;
use App\Services\DocumentoFiscalService;
use App\Services\ServicoPerfilUsuario;
use App\Services\ServicoPrivacidade;
use App\Services\ServicoSegurancaUsuario;
use App\Services\ServicoSessaoUsuario;
use App\Services\WhatsAppLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ConfiguracoesController extends Controller
{
    public function perfil()
    {
        return view('interno.configuracoes.perfil', ['user' => auth()->user()->load('corretorPerfil')]);
    }

    public function atualizarPerfil(Request $request, ServicoPerfilUsuario $service)
    {
        $dados = $request->validate([
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remover_foto' => ['nullable', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
            'bio' => ['required', 'string', 'max:700'],
            'especialidades' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:120'],
            'estado' => ['required', 'string', 'size:2'],
        ], [
            'telefone.required' => 'Informe seu telefone de contato.',
            'bio.required' => 'Informe uma biografia para o seu perfil público.',
            'especialidades.required' => 'Informe pelo menos uma especialidade.',
            'cidade.required' => 'Informe sua cidade de atendimento.',
            'estado.required' => 'Informe o estado de atendimento.',
            'estado.size' => 'Informe o estado com 2 letras, como SP.',
        ]);

        $service->atualizar(auth()->user(), $dados, $request);

        return back()->with('status', 'Perfil atualizado.');
    }

    public function seguranca()
    {
        return view('interno.configuracoes.seguranca', ['user' => auth()->user(), 'sessoes' => auth()->user()->sessoesUsuario()->latest('ultima_atividade_em')->get()]);
    }

    public function atualizarSenha(Request $request, ServicoSegurancaUsuario $service, ServicoSessaoUsuario $sessoes)
    {
        $dados = $request->validate([
            'senha_atual' => ['required'],
            'password' => ['required', 'string', PasswordRule::min(8)->mixedCase()->letters()->numbers()->symbols(), 'confirmed'],
        ]);

        $service->alterarSenha(auth()->user(), $dados['senha_atual'], $dados['password']);
        $sessoes->encerrarOutrasSessoes(auth()->user(), $request->session()->getId());

        return back()->with('status', 'Senha alterada e outras sessões encerradas.');
    }

    public function assinatura()
    {
        return view('interno.configuracoes.assinatura', ['assinatura' => auth()->user()->assinatura]);
    }

    public function preferencias()
    {
        return view('interno.configuracoes.preferencias', ['user' => auth()->user()]);
    }

    public function mensagemWhatsapp(WhatsAppLinkService $whatsApp)
    {
        $perfil = $this->perfilDoCorretor();
        $mensagem = $perfil->mensagem_primeiro_contato_whatsapp ?: WhatsAppLinkService::DEFAULT_LEAD_TEMPLATE;
        $mensagemContrato = $perfil->mensagem_contrato_vigente_whatsapp ?: WhatsAppLinkService::DEFAULT_CONTRACT_TEMPLATE;
        $leadPreview = new Indicacao([
            'nome_cliente' => 'Fernando Diniz',
            'telefone' => '(11) 99953-5578',
            'tipo_plano' => 'PME',
            'quantidade_vidas' => 11,
            'cidade' => 'Sao Paulo',
            'estado' => 'SP',
        ]);

        $avaliacaoPreview = new \App\Models\AvaliacaoAtendimento();
        $clientePreview = new \App\Models\Cliente([
            'nome' => 'Fernando Diniz',
            'telefone' => '(11) 99953-5578',
            'email' => 'fernando@email.com',
            'inicio_vigencia' => now(),
        ]);
        $avaliacaoPreview->setRelation('cliente', $clientePreview);
        $avaliacaoPreview->setRelation('indicacao', $leadPreview);

        return view('interno.configuracoes.mensagem-whatsapp', [
            'perfil' => $perfil,
            'mensagem' => $mensagem,
            'mensagemContrato' => $mensagemContrato,
            'preview' => $whatsApp->parseMessageTemplate($mensagem, $leadPreview),
            'previewContrato' => $whatsApp->parseContractTemplate($mensagemContrato, $avaliacaoPreview, 'https://nexosaude.com.br/avaliacao/exemplo'),
            'variaveis' => ['{nome}', '{telefone}', '{tipo_plano}', '{quantidade_vidas}', '{cidade}', '{estado}'],
            'variaveisContrato' => ['{nome}', '{telefone}', '{email}', '{data_vigencia}', '{tipo_plano}', '{quantidade_vidas}', '{link_avaliacao}'],
        ]);
    }

    public function atualizarMensagemWhatsapp(Request $request)
    {
        $dados = $request->validate([
            'mensagem_primeiro_contato_whatsapp' => ['required', 'string', 'max:500'],
            'mensagem_contrato_vigente_whatsapp' => ['required', 'string', 'max:900'],
        ], [
            'mensagem_primeiro_contato_whatsapp.required' => 'Informe a mensagem padrao de primeiro contato.',
            'mensagem_primeiro_contato_whatsapp.max' => 'A mensagem de primeiro contato deve ter no maximo 500 caracteres.',
            'mensagem_contrato_vigente_whatsapp.required' => 'Informe a mensagem de contrato vigente e avaliação.',
            'mensagem_contrato_vigente_whatsapp.max' => 'A mensagem de contrato vigente deve ter no maximo 900 caracteres.',
        ]);

        $this->perfilDoCorretor()->update($dados);

        return back()->with('status', 'Mensagens de WhatsApp salvas.');
    }

    public function atualizarPreferencias(Request $request)
    {
        auth()->user()->update($request->validate([
            'receber_alertas_email' => ['nullable', 'boolean'],
            'receber_notificacoes_aniversario' => ['nullable', 'boolean'],
            'receber_notificacoes_renovacao' => ['nullable', 'boolean'],
            'receber_notificacoes_tarefas' => ['nullable', 'boolean'],
            'timezone' => ['required', 'string', 'max:80'],
            'idioma' => ['required', 'string', 'max:20'],
            'formato_data' => ['required', 'string', 'max:20'],
        ]));

        return back()->with('status', 'Preferências atualizadas.');
    }

    public function privacidade()
    {
        return view('interno.configuracoes.privacidade');
    }

    public function sessoes(Request $request, ServicoSessaoUsuario $service)
    {
        $service->registrarAcesso(auth()->user(), $request);

        return view('interno.configuracoes.sessoes', ['sessoes' => auth()->user()->sessoesUsuario()->latest('ultima_atividade_em')->paginate(10)->withQueryString()]);
    }

    public function encerrarOutrasSessoes(Request $request, ServicoSessaoUsuario $service)
    {
        $service->encerrarOutrasSessoes(auth()->user(), $request->session()->getId());

        return back()->with('status', 'Outras sessões encerradas.');
    }

    public function excluir()
    {
        return view('interno.configuracoes.excluir-conta');
    }

    public function destruirConta(Request $request, ServicoPrivacidade $service)
    {
        $dados = $request->validate([
            'senha' => ['required'],
            'confirmacao' => ['required', 'string'],
        ]);

        $service->excluirConta(auth()->user(), $dados['senha'], $dados['confirmacao']);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Conta excluída.');
    }

    private function perfilDoCorretor()
    {
        $user = auth()->user();

        return $user->corretorPerfil()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => CorretorPerfil::gerarSlugPublico($user->name),
                'public_hash' => CorretorPerfil::gerarHashPublico(),
                'nome_publico' => $user->name,
                'mensagem_primeiro_contato_whatsapp' => WhatsAppLinkService::DEFAULT_LEAD_TEMPLATE,
                'mensagem_contrato_vigente_whatsapp' => WhatsAppLinkService::DEFAULT_CONTRACT_TEMPLATE,
            ]
        );
    }

    public function atualizarCartaoAssinatura(Request $request, \App\Services\Asaas\AsaasSubscriptionService $asaasSubscriptionService)
    {
        $dados = $request->validate([
            'billing_cpf_cnpj' => ['required', 'string', 'max:20', new CpfCnpjValido],
            'card_holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'max:30'],
            'card_expiry_month' => ['required', 'string', 'size:2'],
            'card_expiry_year' => ['required', 'string', 'size:4'],
            'card_ccv' => ['required', 'string', 'min:3', 'max:4'],
            'holder_postal_code' => ['nullable', 'string', 'max:20'],
            'holder_address_number' => ['nullable', 'string', 'max:20'],
        ]);

        $dados['billing_cpf_cnpj'] = app(DocumentoFiscalService::class)->normalizar($dados['billing_cpf_cnpj']);

        $user = auth()->user();
        $assinatura = $user->assinatura;

        if (! $assinatura || ! $assinatura->asaas_subscription_id) {
            return back()->withErrors([
                'assinatura' => 'Não encontramos uma assinatura válida para atualizar o cartão.',
            ]);
        }

        $response = $asaasSubscriptionService->updateCreditCard($assinatura->asaas_subscription_id, [
            'card_holder_name' => $dados['card_holder_name'],
            'card_number' => $dados['card_number'],
            'card_expiry_month' => $dados['card_expiry_month'],
            'card_expiry_year' => $dados['card_expiry_year'],
            'card_ccv' => $dados['card_ccv'],

            'holder_name' => $user->name,
            'holder_email' => $user->email,
            'holder_cpf_cnpj' => $dados['billing_cpf_cnpj'],
            'holder_phone' => $user->telefone,
            'holder_postal_code' => $dados['holder_postal_code'] ?? '01001000',
            'holder_address_number' => $dados['holder_address_number'] ?? '100',
            'remote_ip' => $request->ip(),
        ]);

        if (! ($response['success'] ?? false)) {
            return back()
                ->withInput($request->except(['card_number', 'card_ccv']))
                ->withErrors([
                    'cartao' => 'Não foi possível atualizar o cartão. Verifique os dados informados e tente novamente.',
                ]);
        }

        $asaasData = $response['data'] ?? [];
        $creditCard = $asaasData['creditCard'] ?? [];

        $assinatura->update([
            'card_brand' => $creditCard['creditCardBrand'] ?? $assinatura->card_brand,
            'card_last_four' => $creditCard['creditCardNumber'] ?? substr(preg_replace('/\D/', '', $dados['card_number']), -4),
            'card_token' => $creditCard['creditCardToken'] ?? $assinatura->card_token,
            'gateway_payload' => \App\Models\Assinatura::sanitizarGatewayPayload($asaasData),
        ]);

        return back()->with('status', 'Cartão atualizado com sucesso.');
    }

    public function cancelarAssinatura(Request $request, \App\Services\Asaas\AsaasSubscriptionService $asaasSubscriptionService)
    {
        $request->validate([
            'confirmar_cancelamento' => ['accepted'],
        ], [
            'confirmar_cancelamento.accepted' => 'Confirme que deseja cancelar a assinatura.',
        ]);

        $user = auth()->user();
        $assinatura = $user->assinatura;

        if (! $assinatura || ! $assinatura->asaas_subscription_id) {
            return back()->withErrors([
                'assinatura' => 'Não encontramos uma assinatura válida para cancelar.',
            ]);
        }

        $response = $asaasSubscriptionService->cancel($assinatura->asaas_subscription_id);

        if (! ($response['success'] ?? false)) {
            return back()->withErrors([
                'assinatura' => 'Não foi possível cancelar a assinatura no momento. Tente novamente.',
            ]);
        }

        $assinatura->update([
            'status' => 'canceled',
            'status_assinatura' => 'cancelada',
            'canceled_at' => now(),
            'gateway_payload' => \App\Models\Assinatura::sanitizarGatewayPayload($response['data'] ?? $response),
        ]);

        $user->update([
            'billing_status' => 'canceled',
            'subscription_canceled_at' => now(),
        ]);

        return redirect()
            ->route('assinatura.bloqueada')
            ->with('status', 'Assinatura cancelada com sucesso.');
    }
}
