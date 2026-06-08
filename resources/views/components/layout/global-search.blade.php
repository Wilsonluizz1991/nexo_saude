@php
    $user = auth()->user();
    $isAdmin = (bool) ($user?->is_admin || $user?->perfil === 'admin');
    $adminSearchRoute = request()->routeIs('admin.auditoria.*') ? route('admin.auditoria.index') : route('admin.usuarios.index');
    $adminSearchPlaceholder = request()->routeIs('admin.auditoria.*')
        ? 'Buscar auditorias, ações, administradores...'
        : 'Buscar usuários, perfis e assinaturas...';
@endphp

<form
    class="nexo-sidebar-search"
    role="search"
    method="GET"
    action="{{ $isAdmin ? $adminSearchRoute : route('busca.index') }}"
    @unless($isAdmin) data-global-search-form @endunless
>
    <i data-lucide="search" class="nexo-sidebar-search-icon" aria-hidden="true"></i>
    <input
        type="search"
        name="q"
        value="{{ request('q') }}"
        placeholder="{{ $isAdmin ? $adminSearchPlaceholder : 'Buscar leads, clientes, propostas...' }}"
        aria-label="{{ $isAdmin ? $adminSearchPlaceholder : 'Buscar leads, clientes, propostas' }}"
        autocomplete="off"
        @unless($isAdmin) data-global-search-input @endunless
    >
</form>
