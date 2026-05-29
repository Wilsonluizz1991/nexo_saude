<?php

use App\Http\Controllers\Admin\AdminSistemaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Interno\AssinaturaController;
use App\Http\Controllers\Interno\BuscaGlobalController;
use App\Http\Controllers\Interno\ClienteController;
use App\Http\Controllers\Interno\Configuracoes\ConfiguracoesController;
use App\Http\Controllers\Interno\DashboardController;
use App\Http\Controllers\Interno\ImplantacaoController;
use App\Http\Controllers\Interno\IndicacaoController;
use App\Http\Controllers\Interno\PaginaController;
use App\Http\Controllers\Interno\PerfilPublicoController;
use App\Http\Controllers\Interno\PreCadastroController;
use App\Http\Controllers\Interno\PropostaController;
use App\Http\Controllers\Publico\AvaliacaoAtendimentoController;
use App\Http\Controllers\Publico\DocumentoClienteController;
use App\Http\Controllers\Publico\PaginaCorretorController;
use App\Http\Controllers\Webhook\AsaasWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/criar-conta', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/criar-conta', [AuthController::class, 'register'])->name('register.store');
    Route::get('/entrar', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/entrar', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/sair', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/perfil/{slug}', [PaginaCorretorController::class, 'show'])->name('publico.corretor');
Route::post('/perfil/{slug}/solicitacao', [PaginaCorretorController::class, 'store'])->name('publico.indicacoes.store');
Route::get('/perfil-corretor/{slug}', [PaginaCorretorController::class, 'showAntigo'])->name('publico.corretor.antigo');
Route::post('/perfil-corretor/{slug}/solicitacao', [PaginaCorretorController::class, 'storeAntigo'])->name('publico.indicacoes.store.antigo');

Route::get('/avaliacao/{token}', [AvaliacaoAtendimentoController::class, 'show'])->name('publico.avaliacoes.show');
Route::post('/avaliacao/{token}', [AvaliacaoAtendimentoController::class, 'store'])->name('publico.avaliacoes.store');

Route::get('/{slug}/pre-cadastro/{token}', [DocumentoClienteController::class, 'show'])->name('cliente.pre-cadastro.show');
Route::post('/{slug}/pre-cadastro/{token}/validar-acesso', [DocumentoClienteController::class, 'validarAcesso'])->name('cliente.pre-cadastro.validar-acesso');
Route::post('/{slug}/pre-cadastro/{token}', [DocumentoClienteController::class, 'store'])->name('cliente.pre-cadastro.store');

Route::get('/cliente/documentos/{token}', [DocumentoClienteController::class, 'showAntigo'])->name('cliente.documentos.show');
Route::post('/cliente/documentos/{token}', [DocumentoClienteController::class, 'storeAntigo'])->name('cliente.documentos.store');

Route::post('/webhooks/asaas', [AsaasWebhookController::class, 'handle']);

Route::middleware(['auth', 'usuario.ativo'])->group(function () {
    Route::get('/assinatura', [AssinaturaController::class, 'bloqueada'])->name('assinatura.bloqueada');
    Route::post('/assinatura/assinar', [AssinaturaController::class, 'assinar'])->name('assinatura.assinar');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'usuario.ativo', 'admin.sistema'])
    ->group(function () {
        Route::get('/', [AdminSistemaController::class, 'dashboard'])->name('dashboard');

        Route::get('/usuarios', [AdminSistemaController::class, 'usuarios'])->name('usuarios.index');
        Route::get('/usuarios/novo', [AdminSistemaController::class, 'criarUsuario'])->name('usuarios.create');
        Route::post('/usuarios', [AdminSistemaController::class, 'salvarUsuario'])->name('usuarios.store');
        Route::get('/usuarios/{usuario}/editar', [AdminSistemaController::class, 'editarUsuario'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [AdminSistemaController::class, 'atualizarUsuario'])->name('usuarios.update');
        Route::post('/usuarios/{usuario}/bloquear', [AdminSistemaController::class, 'bloquearUsuario'])->name('usuarios.bloquear');
        Route::post('/usuarios/{usuario}/desbloquear', [AdminSistemaController::class, 'desbloquearUsuario'])->name('usuarios.desbloquear');
        Route::delete('/usuarios/{usuario}', [AdminSistemaController::class, 'excluirUsuario'])->name('usuarios.destroy');
        Route::get('/auditoria', [AdminSistemaController::class, 'auditoria'])->name('auditoria.index');
    });

Route::middleware(['auth', 'usuario.ativo', 'assinatura.ativa'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/busca', BuscaGlobalController::class)->name('busca.index');
    Route::post('/assinatura/reativar', [AssinaturaController::class, 'reativar'])->name('assinatura.reativar');
    Route::post('/assinatura/regularizar', [AssinaturaController::class, 'regularizar'])->name('assinatura.regularizar');

    Route::get('/indicacoes', [IndicacaoController::class, 'index'])->name('indicacoes.index');
    Route::get('/indicacoes/nova', [IndicacaoController::class, 'create'])->name('indicacoes.create');
    Route::post('/indicacoes', [IndicacaoController::class, 'store'])->name('indicacoes.store');
    Route::get('/indicacoes/{indicacao}', [IndicacaoController::class, 'show'])->name('indicacoes.show');
    Route::post('/indicacoes/{indicacao}/propostas', [IndicacaoController::class, 'storeProposta'])->name('indicacoes.propostas.store');
    Route::post('/indicacoes/{indicacao}/lembretes', [IndicacaoController::class, 'storeLembrete'])->name('indicacoes.lembretes.store');
    Route::post('/indicacoes/{indicacao}/documentos/{documento}', [IndicacaoController::class, 'atualizarDocumento'])->name('indicacoes.documentos.update');
    Route::post('/indicacoes/{indicacao}/pre-cadastro/correcao', [IndicacaoController::class, 'solicitarCorrecao'])->name('indicacoes.pre-cadastro.correcao');
    Route::post('/indicacoes/{indicacao}/aceitar', [IndicacaoController::class, 'aceitar'])->name('indicacoes.aceitar');
    Route::post('/indicacoes/{indicacao}/implantacao', [IndicacaoController::class, 'iniciarImplantacao'])->name('indicacoes.implantacao.iniciar');
    Route::post('/indicacoes/{indicacao}/implantacao/status', [IndicacaoController::class, 'atualizarStatusImplantacao'])->name('indicacoes.implantacao.status');
    Route::post('/indicacoes/{indicacao}/implantacao/aprovar', [IndicacaoController::class, 'aprovarImplantacao'])->name('indicacoes.implantacao.aprovar');

    Route::get('/implantacoes/{implantacao}', [ImplantacaoController::class, 'show'])->name('implantacoes.show');

    Route::get('/indicacoes/{indicacao}/pre-cadastro', [PreCadastroController::class, 'create'])->name('pre-cadastros.create');
    Route::post('/indicacoes/{indicacao}/pre-cadastro', [PreCadastroController::class, 'store'])->name('pre-cadastros.store');
    Route::get('/pre-cadastros/{preCadastro}', [PreCadastroController::class, 'show'])->name('pre-cadastros.show');

    Route::get('/perfil-publico', [PerfilPublicoController::class, 'edit'])->name('perfil-publico.edit');
    Route::post('/perfil-publico', [PerfilPublicoController::class, 'update'])->name('perfil-publico.update');

    Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
        Route::get('/perfil', [ConfiguracoesController::class, 'perfil'])->name('perfil');
        Route::post('/perfil', [ConfiguracoesController::class, 'atualizarPerfil'])->name('perfil.update');

        Route::get('/seguranca', [ConfiguracoesController::class, 'seguranca'])->name('seguranca');
        Route::post('/seguranca/senha', [ConfiguracoesController::class, 'atualizarSenha'])->name('seguranca.senha');

        Route::get('/assinatura', [ConfiguracoesController::class, 'assinatura'])->name('assinatura');
        Route::post('/assinatura/cartao', [ConfiguracoesController::class, 'atualizarCartaoAssinatura'])->name('assinatura.cartao.update');
        Route::post('/assinatura/cancelar', [ConfiguracoesController::class, 'cancelarAssinatura'])->name('assinatura.cancelar');
       

        Route::get('/preferencias', [ConfiguracoesController::class, 'preferencias'])->name('preferencias');
        Route::post('/preferencias', [ConfiguracoesController::class, 'atualizarPreferencias'])->name('preferencias.update');

        Route::get('/mensagem-whatsapp', [ConfiguracoesController::class, 'mensagemWhatsapp'])->name('mensagem-whatsapp');
        Route::post('/mensagem-whatsapp', [ConfiguracoesController::class, 'atualizarMensagemWhatsapp'])->name('mensagem-whatsapp.update');

        Route::get('/privacidade', [ConfiguracoesController::class, 'privacidade'])->name('privacidade');

        Route::get('/sessoes', [ConfiguracoesController::class, 'sessoes'])->name('sessoes');
        Route::post('/sessoes/encerrar-outras', [ConfiguracoesController::class, 'encerrarOutrasSessoes'])->name('sessoes.encerrar-outras');

        Route::get('/excluir-conta', [ConfiguracoesController::class, 'excluir'])->name('excluir-conta');
        Route::delete('/excluir-conta', [ConfiguracoesController::class, 'destruirConta'])->name('excluir-conta.destroy');
    });

    Route::get('/agenda', [PaginaController::class, 'agenda'])->name('agenda.index');

    Route::get('/tarefas', [PaginaController::class, 'tarefas'])->name('tarefas.index');
    Route::post('/tarefas/{tarefa}/concluir', [PaginaController::class, 'concluirTarefa'])->name('tarefas.concluir');

    Route::get('/alertas', [PaginaController::class, 'alertas'])->name('alertas.index');
    Route::get('/alertas/{alerta}/abrir', [PaginaController::class, 'abrirAlerta'])->name('alertas.abrir');
    Route::post('/alertas/{alerta}/resolver', [PaginaController::class, 'resolverAlerta'])->name('alertas.resolver');

    Route::get('/relatorios', [PaginaController::class, 'relatorios'])->name('relatorios.index');

    Route::post('/carteira/meta-mensal', [PaginaController::class, 'salvarMetaMensal'])->name('carteira.meta-mensal.store');

    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');

    Route::get('/propostas/{indicacao}', [PropostaController::class, 'show'])->name('propostas.show');
    Route::post('/propostas/{indicacao}', [PropostaController::class, 'store'])->name('propostas.store');

    Route::get('/{pagina}', [PaginaController::class, 'simples'])
        ->whereIn('pagina', ['propostas', 'pre-cadastros', 'implantacoes', 'clientes', 'carteira'])
        ->name('paginas.simples');
});
