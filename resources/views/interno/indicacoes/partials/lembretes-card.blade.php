@php
    $lembretesIndicacao = $indicacao->tarefas?->where('tipo', 'lembrete')->sortBy('vencimento') ?? collect();
@endphp

<section class="nexo-reminder-card">
    <div class="nexo-reminder-card-header">
        <div>
            <span class="nexo-section-kicker">
                <i class="bi bi-bell"></i>
                Acompanhamento
            </span>
            <h2>Lembretes</h2>
            <p>Programe retornos, cobranças de documentos e próximos passos deste registro.</p>
        </div>
    </div>

    <form method="post" action="{{ route('indicacoes.lembretes.store', $indicacao) }}" class="nexo-reminder-form">
        @csrf

        <div>
            <label class="form-label">Data do lembrete</label>
            <input class="form-control" name="data_ocorrencia" type="date" min="{{ now()->toDateString() }}" required>
        </div>

        <div>
            <label class="form-label">Informações do lembrete</label>
            <textarea class="form-control" name="descricao" rows="3" maxlength="255" placeholder="Ex.: Retornar com proposta, cobrar documentação ou ligar para alinhamento." required></textarea>
        </div>

        <button class="nexo-primary-btn">
            <i class="bi bi-plus-circle"></i>
            Criar lembrete
        </button>
    </form>

    <div class="nexo-reminder-list">
        @forelse($lembretesIndicacao as $lembrete)
            <div class="nexo-reminder-item">
                <div>
                    <strong>{{ $lembrete->titulo }}</strong>
                    <span>Programado para {{ $lembrete->vencimento?->format('d/m/Y') }}</span>
                </div>

                <small>{{ ucfirst($lembrete->status) }}</small>
            </div>
        @empty
            <div class="nexo-empty-state">
                Nenhum lembrete criado para este registro.
            </div>
        @endforelse
    </div>
</section>
