<x-layouts.public title="Solicitação enviada | Nexo Saúde">
    <header class="nexo-public-header">
        <div class="nexo-public-container">
            <a class="nexo-logo" href="{{ route('publico.corretor', $perfil->slug) }}" aria-label="Nexo Saúde">
                <img src="{{ asset('assets/nexo-logo-topo.png') }}" alt="Nexo Saúde">
            </a>
        </div>
    </header>

    <main class="nexo-public-page">
        <section class="nexo-public-container">
            <div class="nexo-public-card nexo-public-success">
                <div>
                    <span class="status-pill">Solicitação enviada</span>
                    <h1>Recebemos seu pedido.</h1>
                    <p class="muted mb-0">{{ $perfil->nome_publico }} vai analisar suas informações e seguir com o atendimento do seu plano de saúde.</p>
                </div>
                <a class="btn btn-primary" href="{{ route('publico.corretor', $perfil->slug) }}">Voltar ao perfil</a>
            </div>
        </section>
    </main>
</x-layouts.public>
