<x-layouts.app title="Perfil público | Nexo Saúde">
    <main class="nexo-main">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="nexo-card p-4">
                    <h1 class="h3 fw-bold">Perfil público do corretor</h1>
                    <p class="muted">A página pública tem design único. Você só configura foto, nome, bio, especialidades e cidade/região.</p>
                    <form method="post" action="{{ route('perfil-publico.update') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6"><label class="form-label">Slug</label><input name="slug" class="form-control" value="{{ $perfil->slug }}"></div>
                        <div class="col-md-6"><label class="form-label">Foto</label><input name="foto" type="file" class="form-control" accept="image/*"></div>
                        <div class="col-12"><label class="form-label">Nome</label><input name="nome_publico" class="form-control" value="{{ $perfil->nome_publico }}"></div>
                        <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control">{{ $perfil->bio }}</textarea></div>
                        <div class="col-md-6"><label class="form-label">Especialidades</label><input name="especialidades" class="form-control" value="{{ implode(', ', $perfil->especialidades ?? []) }}"></div>
                        <div class="col-md-6"><label class="form-label">Cidade/região</label><input name="cidade_regiao" class="form-control" value="{{ $perfil->cidade_regiao }}"></div>
                        <div class="col-12"><button class="btn btn-primary">Salvar perfil</button></div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="nexo-card p-4">
                    <h2 class="h5 fw-bold">Link público</h2>
                    <a href="{{ route('publico.corretor', $perfil->slug) }}">{{ route('publico.corretor', $perfil->slug) }}</a>
                </div>
            </div>
        </div>
    </main>
</x-layouts.app>
