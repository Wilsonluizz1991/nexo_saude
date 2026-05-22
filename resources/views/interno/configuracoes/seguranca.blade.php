<x-configuracoes.layout titulo="Segurança">
    <h2>Segurança</h2>
    <p class="muted">Último acesso: {{ optional($user->ultimo_login_em)->format('d/m/Y H:i') ?? 'Não registrado' }} · IP: {{ $user->ultimo_ip ?? 'Não registrado' }}</p>
    <form method="post" action="{{ route('configuracoes.seguranca.senha') }}" class="row g-3">
        @csrf
        <div class="col-md-4"><label class="form-label">Senha atual</label><input name="senha_atual" type="password" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Nova senha</label><input name="password" type="password" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Confirmar senha</label><input name="password_confirmation" type="password" class="form-control" required></div>
        <div class="col-12"><button class="btn btn-primary">Alterar senha</button></div>
    </form>
</x-configuracoes.layout>
