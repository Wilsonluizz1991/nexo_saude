<x-layouts.app title="Assinatura | Nexo Saúde">
    <main class="nexo-main">
        <section class="nexo-card p-5">
            <h1 class="h3 fw-bold">Assinatura necessária</h1>
            <p class="muted">Seu teste grátis terminou em {{ optional($assinatura?->data_fim_teste_gratis)->format('d/m/Y') }}. Para acessar as áreas internas, assine por R$ 249,90/mês.</p>
            <form method="post" action="{{ route('assinatura.assinar') }}">@csrf<button class="btn btn-primary">Assinar por R$ 249,90/mês</button></form>
        </section>
    </main>
</x-layouts.app>
