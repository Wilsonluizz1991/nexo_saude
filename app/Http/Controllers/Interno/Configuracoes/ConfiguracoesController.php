<?php

namespace App\Http\Controllers\Interno\Configuracoes;

use App\Http\Controllers\Controller;
use App\Services\ServicoPerfilUsuario;
use App\Services\ServicoPrivacidade;
use App\Services\ServicoSegurancaUsuario;
use App\Services\ServicoSessaoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ConfiguracoesController extends Controller
{
    public function perfil()
    {
        return view('interno.configuracoes.perfil', ['user' => auth()->user()->load('corretorPerfil')]);
    }

    public function atualizarPerfil(Request $request, ServicoPerfilUsuario $service)
    {
        $dados = $request->validate([
            'foto' => ['nullable', 'image', 'max:4096'],
            'remover_foto' => ['nullable', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
            'bio' => ['nullable', 'string', 'max:700'],
            'especialidades' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('corretor_perfis', 'slug')->ignore(auth()->user()->corretorPerfil?->id)],
            'anos_experiencia' => ['nullable', 'integer', 'min:0', 'max:80'],
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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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

        return view('interno.configuracoes.sessoes', ['sessoes' => auth()->user()->sessoesUsuario()->latest('ultima_atividade_em')->get()]);
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
}
