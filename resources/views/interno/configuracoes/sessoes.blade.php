<x-configuracoes.layout titulo="Sessões Ativas">
    <h2>Sessões Ativas</h2>
    <table class="table align-middle"><thead><tr><th>Dispositivo</th><th>Navegador</th><th>Sistema</th><th>IP</th><th>Última atividade</th></tr></thead><tbody>
    @foreach($sessoes as $sessao)
        <tr><td>{{ $sessao->dispositivo }}</td><td>{{ $sessao->navegador }}</td><td>{{ $sessao->sistema_operacional }}</td><td>{{ $sessao->ip }}</td><td>{{ optional($sessao->ultima_atividade_em)->format('d/m/Y H:i') }}</td></tr>
    @endforeach
    </tbody></table>
    <form method="post" action="{{ route('configuracoes.sessoes.encerrar-outras') }}">@csrf<button class="btn btn-outline-danger">Encerrar outras sessões</button></form>
</x-configuracoes.layout>
