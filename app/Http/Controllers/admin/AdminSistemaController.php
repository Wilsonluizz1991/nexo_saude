<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assinatura;
use App\Models\AuditoriaLog;
use App\Models\User;
use App\Services\Admin\RegistrarAuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminSistemaController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'totalUsuarios' => User::count(),
            'usuariosAtivos' => User::whereNull('blocked_at')->count(),
            'usuariosBloqueados' => User::whereNotNull('blocked_at')->count(),
            'admins' => User::where('is_admin', true)->count(),

            'assinaturasAtivas' => Assinatura::where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere('status_assinatura', 'ativa');
            })->count(),

            'assinaturasTeste' => Assinatura::where(function ($query) {
                $query->where('status', 'trialing')
                    ->orWhere('status_assinatura', 'teste_gratis');
            })->count(),

            'assinaturasPendentes' => Assinatura::where(function ($query) {
                $query->whereIn('status', ['overdue', 'past_due', 'dunning'])
                    ->orWhereIn('status_assinatura', ['vencida', 'bloqueada']);
            })->count(),

            'assinaturasCanceladas' => Assinatura::where(function ($query) {
                $query->whereIn('status', ['canceled', 'cancelled'])
                    ->orWhere('status_assinatura', 'cancelada');
            })->count(),

            'receitaMensalPrevista' => Assinatura::where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere('status_assinatura', 'ativa');
            })->sum('valor'),

            'usuariosRecentes' => User::latest()->limit(8)->get(),
            'auditoriasRecentes' => AuditoriaLog::with(['administrador', 'usuarioAlvo'])->latest()->limit(6)->get(),
        ]);
    }

    public function usuarios(Request $request)
    {
        $busca = trim((string) $request->get('q'));

        $usuarios = User::with('assinatura')
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($subquery) use ($busca) {
                    $subquery->where('name', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%")
                        ->orWhere('perfil', 'like', "%{$busca}%")
                        ->orWhereHas('assinatura', function ($assinaturaQuery) use ($busca) {
                            $assinaturaQuery->where('status', 'like', "%{$busca}%")
                                ->orWhere('status_assinatura', 'like', "%{$busca}%")
                                ->orWhere('asaas_customer_id', 'like', "%{$busca}%")
                                ->orWhere('asaas_subscription_id', 'like', "%{$busca}%");
                        });
                });
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.usuarios.partials.table', [
                'usuarios' => $usuarios,
            ]);
        }

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'busca' => $busca,
        ]);
    }

    public function auditoria(Request $request)
    {
        $busca = trim((string) $request->get('q'));

        $logs = AuditoriaLog::with(['administrador', 'usuarioAlvo'])
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($subquery) use ($busca) {
                    $subquery->where('acao', 'like', "%{$busca}%")
                        ->orWhere('modulo', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%")
                        ->orWhereHas('administrador', function ($adminQuery) use ($busca) {
                            $adminQuery->where('name', 'like', "%{$busca}%")
                                ->orWhere('email', 'like', "%{$busca}%");
                        })
                        ->orWhereHas('usuarioAlvo', function ($usuarioQuery) use ($busca) {
                            $usuarioQuery->where('name', 'like', "%{$busca}%")
                                ->orWhere('email', 'like', "%{$busca}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.auditoria.partials.table', [
                'logs' => $logs,
            ]);
        }

        return view('admin.auditoria.index', [
            'logs' => $logs,
            'busca' => $busca,
        ]);
    }

    public function criarUsuario()
    {
        return view('admin.usuarios.form', [
            'usuario' => new User(),
        ]);
    }

    public function salvarUsuario(Request $request, RegistrarAuditoriaService $auditoria)
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'perfil' => ['nullable', 'string', 'max:50'],
            'is_admin' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $usuario = User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'perfil' => $dados['perfil'] ?? 'corretor',
            'is_admin' => (bool) ($dados['is_admin'] ?? false),
            'admin_since' => ! empty($dados['is_admin']) ? now() : null,
            'password' => Hash::make($dados['password']),
        ]);

        $auditoria->registrar(
            'usuario_criado',
            $usuario,
            "Usuário {$usuario->name} foi criado pelo administrador.",
            null,
            $usuario->only(['id', 'name', 'email', 'telefone', 'perfil', 'is_admin'])
        );

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuário criado com sucesso.');
    }

    public function editarUsuario(User $usuario)
    {
        return view('admin.usuarios.form', [
            'usuario' => $usuario,
        ]);
    }

    public function atualizarUsuario(Request $request, User $usuario, RegistrarAuditoriaService $auditoria)
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:30'],
            'perfil' => ['nullable', 'string', 'max:50'],
            'is_admin' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $antes = $usuario->only(['id', 'name', 'email', 'telefone', 'perfil', 'is_admin', 'blocked_at']);

        $usuario->update([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'perfil' => $dados['perfil'] ?? $usuario->perfil,
            'is_admin' => (bool) ($dados['is_admin'] ?? false),
            'admin_since' => ! empty($dados['is_admin']) && ! $usuario->admin_since ? now() : $usuario->admin_since,
            'password' => ! empty($dados['password']) ? Hash::make($dados['password']) : $usuario->password,
        ]);

        $usuario->refresh();

        $auditoria->registrar(
            'usuario_atualizado',
            $usuario,
            "Usuário {$usuario->name} foi atualizado pelo administrador.",
            $antes,
            $usuario->only(['id', 'name', 'email', 'telefone', 'perfil', 'is_admin', 'blocked_at'])
        );

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuário atualizado com sucesso.');
    }

    public function bloquearUsuario(User $usuario, RegistrarAuditoriaService $auditoria)
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors([
                'usuario' => 'Você não pode bloquear sua própria conta administradora.',
            ]);
        }

        $antes = $usuario->only(['id', 'name', 'email', 'blocked_at']);

        $usuario->update([
            'blocked_at' => now(),
        ]);

        $usuario->refresh();

        $auditoria->registrar(
            'usuario_bloqueado',
            $usuario,
            "Usuário {$usuario->name} foi bloqueado pelo administrador.",
            $antes,
            $usuario->only(['id', 'name', 'email', 'blocked_at'])
        );

        return back()->with('status', 'Usuário bloqueado.');
    }

    public function desbloquearUsuario(User $usuario, RegistrarAuditoriaService $auditoria)
    {
        $antes = $usuario->only(['id', 'name', 'email', 'blocked_at']);

        $usuario->update([
            'blocked_at' => null,
        ]);

        $usuario->refresh();

        $auditoria->registrar(
            'usuario_desbloqueado',
            $usuario,
            "Usuário {$usuario->name} foi desbloqueado pelo administrador.",
            $antes,
            $usuario->only(['id', 'name', 'email', 'blocked_at'])
        );

        return back()->with('status', 'Usuário desbloqueado.');
    }

    public function excluirUsuario(User $usuario, RegistrarAuditoriaService $auditoria)
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors([
                'usuario' => 'Você não pode excluir sua própria conta administradora.',
            ]);
        }

        $antes = $usuario->only(['id', 'name', 'email', 'telefone', 'perfil', 'is_admin', 'blocked_at']);

        $auditoria->registrar(
            'usuario_excluido',
            $usuario,
            "Usuário {$usuario->name} foi excluído pelo administrador.",
            $antes,
            null
        );

        $usuario->delete();

        return back()->with('status', 'Usuário excluído.');
    }
}