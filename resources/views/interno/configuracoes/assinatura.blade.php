<x-configuracoes.layout titulo="Assinatura">
    <h2>Assinatura</h2>
    <div class="row g-3">
        <div class="col-md-3"><div class="metric nexo-card"><span>Status</span><h3>{{ $assinatura->status_assinatura }}</h3></div></div>
        <div class="col-md-3"><div class="metric nexo-card"><span>Valor mensal</span><h3>R$ {{ number_format($assinatura->valor_assinatura, 2, ',', '.') }}</h3></div></div>
        <div class="col-md-3"><div class="metric nexo-card"><span>Vencimento</span><h3>{{ optional($assinatura->vencimento_assinatura)->format('d/m/Y') }}</h3></div></div>
        <div class="col-md-3"><div class="metric nexo-card"><span>Plano</span><h3>Corretor Pro</h3></div></div>
    </div>
    <div class="mt-4 d-flex gap-2">
        <form method="post" action="{{ route('assinatura.assinar') }}">@csrf<button class="btn btn-primary">Assinar agora</button></form>
        <button class="btn btn-outline-primary" type="button">Atualizar assinatura</button>
        <button class="btn btn-outline-danger" type="button">Cancelar assinatura</button>
    </div>
</x-configuracoes.layout>
