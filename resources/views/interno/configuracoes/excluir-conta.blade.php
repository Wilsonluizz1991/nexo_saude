<x-configuracoes.layout titulo="Excluir Conta">
    <h2>Excluir Conta</h2>
    <p class="muted">Esta ação cancela a assinatura, oculta o perfil público, invalida sessões e impede novo acesso à conta.</p>
    <form method="post" action="{{ route('configuracoes.excluir-conta.destroy') }}" class="row g-3">
        @csrf
        @method('DELETE')
        <div class="col-md-6"><label class="form-label">Senha atual</label><input name="senha" type="password" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Digite EXCLUIR MINHA CONTA</label><input name="confirmacao" class="form-control" required></div>
        <div class="col-12"><button class="btn btn-danger">Excluir minha conta</button></div>
    </form>
</x-configuracoes.layout>
