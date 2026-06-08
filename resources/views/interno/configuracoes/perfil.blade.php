<x-configuracoes.layout titulo="Meu Perfil">
    <h2>Meu Perfil</h2>
    <form method="post" enctype="multipart/form-data" action="{{ route('configuracoes.perfil.update') }}" class="row g-3">
        @csrf
        <div class="col-md-4">
            <img
                class="nexo-avatar-preview {{ $user->avatar_path ? '' : 'nexo-avatar-preview-logo' }}"
                src="{{ $user->avatar_path ? asset('storage/'.$user->avatar_path) : asset('assets/nexo-logo-topo.png') }}"
                alt="{{ $user->avatar_path ? 'Preview da foto' : 'Logo Nexo Saúde' }}"
            >
            <x-file-input name="foto" accept="image/*" class="mt-3" />
            <label class="form-check mt-2"><input name="remover_foto" value="1" type="checkbox" class="form-check-input"> Remover foto</label>
        </div>
        <div class="col-md-8 row g-3">
            <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" value="{{ $user->name }}" required></div>
            <div class="col-md-6"><label class="form-label">E-mail</label><input name="email" type="email" class="form-control" value="{{ $user->email }}" required></div>
            <div class="col-md-6"><label class="form-label">Telefone</label><input name="telefone" class="form-control" value="{{ $user->telefone }}"></div>
            <div class="col-md-6"><label class="form-label">Link público</label><input class="form-control" value="{{ route('publico.corretor', $user->corretorPerfil->slug) }}" readonly></div>
            <div class="col-12"><label class="form-label">Biografia</label><textarea name="bio" class="form-control">{{ $user->corretorPerfil->bio }}</textarea></div>
            <div class="col-md-6"><label class="form-label">Especialidades</label><input name="especialidades" class="form-control" value="{{ implode(', ', $user->corretorPerfil->especialidades ?? []) }}"></div>
            <div class="col-md-3"><label class="form-label">Cidade</label><input name="cidade" class="form-control" value="{{ $user->corretorPerfil->cidade }}"></div>
            <div class="col-md-3"><label class="form-label">Estado</label><input name="estado" maxlength="2" class="form-control" value="{{ $user->corretorPerfil->estado }}"></div>
            <div class="col-md-4"><label class="form-label">Anos de experiência</label><input name="anos_experiencia" type="number" class="form-control" value="{{ $user->corretorPerfil->anos_experiencia }}"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary">Salvar perfil</button><a target="blank" class="btn btn-outline-primary" href="{{ route('publico.corretor', $user->corretorPerfil->slug) }}">Visualizar perfil público</a></div>
        </div>
    </form>

    <style>
        .nexo-avatar-preview-logo {
            padding: 18px;
            object-fit: contain;
            background: #0B2448;
        }
    </style>
</x-configuracoes.layout>
