<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CriarContaRequest;
use App\Models\User;
use App\Services\AssinaturaService;
use App\Services\ServicoSessaoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(CriarContaRequest $request, AssinaturaService $assinaturaService)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefone' => $request->telefone,
            'password' => Hash::make($request->password),
        ]);

        $assinaturaService->iniciarTesteGratis($user);
        $user->corretorPerfil()->create([
            'slug' => Str::slug($user->name).'-'.$user->id,
            'nome_publico' => $user->name,
            'bio' => 'Especialista em planos de saúde.',
            'especialidades' => ['Planos individuais', 'Planos familiares'],
            'cidade_regiao' => 'São Paulo e região',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request, ServicoSessaoUsuario $sessoes)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            $sessoes->registrarAcesso($request->user(), $request);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'E-mail ou senha inválidos.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
