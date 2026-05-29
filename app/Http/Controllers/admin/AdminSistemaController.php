<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assinatura;
use App\Models\User;
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
                    ->orWhere('status_assinatura', 'inadimplente');
            })->count(),

            'receitaMensalPrevista' => Assinatura::where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere('status_assinatura', 'ativa');
            })->sum('valor'),

            'usuariosRecentes' => User::latest()->limit(8)->get(),
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

    public function criarUsuario()
    {
        return view('admin.usuarios.form', [
            'usuario' => new User(),
        ]);
    }

    public function salvarUsuario(Request $request)
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'perfil' => ['nullable', 'string', 'max:50'],
            'is_admin' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'perfil' => $dados['perfil'] ?? 'corretor',
            'is_admin' => (bool) ($dados['is_admin'] ?? false),
            'admin_since' => ! empty($dados['is_admin']) ? now() : null,
            'password' => Hash::make($dados['password']),
        ]);

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuário criado com sucesso.');
    }

    public function editarUsuario(User $usuario)
    {
        return view('admin.usuarios.form', [
            'usuario' => $usuario,
        ]);
    }

    public function atualizarUsuario(Request $request, User $usuario)
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($usuario->id)],
            'telefone' => ['nullable', 'string', 'max:30'],
            'perfil' => ['nullable', 'string', 'max:50'],
            'is_admin' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $usuario->update([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'telefone' => $dados['telefone'] ?? null,
            'perfil' => $dados['perfil'] ?? $usuario->perfil,
            'is_admin' => (bool) ($dados['is_admin'] ?? false),
            'admin_since' => ! empty($dados['is_admin']) && ! $usuario->admin_since ? now() : $usuario->admin_since,
            'password' => ! empty($dados['password']) ? Hash::make($dados['password']) : $usuario->password,
        ]);

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuário atualizado com sucesso.');
    }

    public function bloquearUsuario(User $usuario)
    {
        $usuario->update([
            'blocked_at' => now(),
        ]);

        return back()->with('status', 'Usuário bloqueado.');
    }

    public function desbloquearUsuario(User $usuario)
    {
        $usuario->update([
            'blocked_at' => null,
        ]);

        return back()->with('status', 'Usuário desbloqueado.');
    }

    public function excluirUsuario(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->withErrors([
                'usuario' => 'Você não pode excluir sua própria conta administradora.',
            ]);
        }

        $usuario->delete();

        return back()->with('status', 'Usuário excluído.');
    }
}