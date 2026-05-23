<x-layouts.app title="Assinatura | Nexo Saude">
    <main class="nexo-main">
        <section class="nexo-card p-5">
            <h1 class="h3 fw-bold">Assinatura necessaria</h1>
            <p class="muted">Seu teste gratis terminou em {{ optional($assinatura?->data_fim_teste_gratis)->format('d/m/Y') }}. Para acessar as areas internas, assine por R$ 49,90/mes.</p>
            <form method="post" action="{{ route('assinatura.assinar') }}">
                @csrf
                <button class="btn btn-primary">Assinar por R$ 49,90/mes</button>
            </form>
        </section>
    </main>
</x-layouts.app>
