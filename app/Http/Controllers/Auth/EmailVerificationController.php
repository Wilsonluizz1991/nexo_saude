<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return view('auth.verify-email');
    }

    public function verify(Request $request, string $id, string $hash)
    {
        $user = User::findOrFail($id);

        abort_unless($request->user()?->is($user), 403);
        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($this->redirectAposVerificacao($user))
            ->with('status', 'E-mail confirmado com sucesso.');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectAposVerificacao($user));
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Enviamos um novo link de confirmacao para seu e-mail.');
    }

    private function redirectAposVerificacao(User $user): string
    {
        if ($user->is_admin || $user->perfil === 'admin') {
            return route('admin.dashboard');
        }

        return session()->pull('email_verification_after', route('perfil-publico.edit'));
    }
}
