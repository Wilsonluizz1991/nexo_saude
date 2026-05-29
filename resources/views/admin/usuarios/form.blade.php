<x-layouts.app title="Usuário | Admin Nexo">
    <div class="container py-4">
        <div class="mb-4">
            <h1 class="fw-bold">
                {{ $usuario->exists ? 'Editar usuário' : 'Novo usuário' }}
            </h1>

            <p class="text-muted mb-0">
                Configure dados de acesso, perfil e permissão administrativa.
            </p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form method="post" action="{{ $usuario->exists ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}">
                    @csrf

                    @if($usuario->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input name="name" class="form-control" value="{{ old('name', $usuario->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input name="email" type="email" class="form-control" value="{{ old('email', $usuario->email) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input name="telefone" class="form-control" value="{{ old('telefone', $usuario->telefone) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Perfil</label>
                            <select name="perfil" class="form-select">
                                <option value="corretor" @selected(old('perfil', $usuario->perfil) === 'corretor')>Corretor</option>
                                <option value="suporte" @selected(old('perfil', $usuario->perfil) === 'suporte')>Suporte</option>
                                <option value="admin" @selected(old('perfil', $usuario->perfil) === 'admin')>Administrador</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                {{ $usuario->exists ? 'Nova senha opcional' : 'Senha' }}
                            </label>
                            <input name="password" type="password" class="form-control" {{ $usuario->exists ? '' : 'required' }}>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <label class="form-check">
                                <input type="checkbox" name="is_admin" value="1" class="form-check-input" @checked(old('is_admin', $usuario->is_admin))>
                                <span class="form-check-label">Administrador do sistema</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary">
                            Salvar
                        </button>

                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">
                            Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>