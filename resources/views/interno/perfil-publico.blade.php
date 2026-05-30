<x-layouts.app title="Perfil público | Nexo Saúde">
    <main class="nexo-main">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="nexo-card p-4">
                    <h1 class="h3 fw-bold">Perfil público do corretor</h1>
                    <p class="muted">A página pública tem design único. Você só configura foto, nome, bio, especialidades e cidade/região.</p>

                    @if(session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('perfil-publico.update') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf

                        <div class="col-md-6">
                            <label class="form-label">Foto</label>

                            @if($perfil->foto_path)
                                <div class="mb-3">
                                    <img
                                        class="nexo-public-profile-photo-preview"
                                        src="{{ asset('storage/'.$perfil->foto_path) }}"
                                        alt="Foto atual do corretor"
                                    >
                                </div>
                            @endif

                            <input name="foto" type="file" class="form-control" accept="image/*">
                            <small class="text-muted d-block mt-2">Use uma foto vertical ou quadrada. O sistema prioriza o rosto no enquadramento.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nome</label>
                            <input name="nome_publico" class="form-control" value="{{ old('nome_publico', $perfil->nome_publico) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control">{{ old('bio', $perfil->bio) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Especialidades</label>
                            <input name="especialidades" class="form-control" value="{{ old('especialidades', implode(', ', $perfil->especialidades ?? [])) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cidade/região</label>
                            <input name="cidade_regiao" class="form-control" value="{{ old('cidade_regiao', $perfil->cidade_regiao) }}">
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary">Salvar perfil</button>
                        </div>
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

    <style>
        .nexo-public-profile-photo-preview {
            width: 132px;
            height: 132px;
            object-fit: cover;
            object-position: center 18%;
            border-radius: 24px;
            border: 1px solid #DDE8F5;
            box-shadow: 0 12px 28px rgba(15, 58, 104, 0.12);
        }
    </style>
</x-layouts.app>
