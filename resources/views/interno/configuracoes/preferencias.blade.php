<x-configuracoes.layout titulo="Preferências">
    <h2>Preferências</h2>
    <form method="post" action="{{ route('configuracoes.preferencias.update') }}" class="row g-3">
        @csrf
        @foreach(['receber_alertas_email' => 'Receber alertas por e-mail', 'receber_notificacoes_aniversario' => 'Notificações de aniversários', 'receber_notificacoes_renovacao' => 'Notificações de renovação', 'receber_notificacoes_tarefas' => 'Notificações de tarefas'] as $campo => $label)
            <div class="col-md-6"><label class="form-check"><input type="hidden" name="{{ $campo }}" value="0"><input class="form-check-input" type="checkbox" name="{{ $campo }}" value="1" @checked($user->{$campo})> {{ $label }}</label></div>
        @endforeach
        <div class="col-md-4"><label class="form-label">Fuso horário</label><input name="timezone" class="form-control" value="{{ $user->timezone }}"></div>
        <div class="col-md-4"><label class="form-label">Idioma</label><input name="idioma" class="form-control" value="{{ $user->idioma }}"></div>
        <div class="col-md-4"><label class="form-label">Formato de data</label><input name="formato_data" class="form-control" value="{{ $user->formato_data }}"></div>
        <div class="col-12"><button class="btn btn-primary">Salvar preferências</button></div>
    </form>
</x-configuracoes.layout>
