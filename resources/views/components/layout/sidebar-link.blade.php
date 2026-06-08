@props([
    'href' => '#',
    'icon' => 'circle',
    'active' => false,
    'badge' => null,
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'nexo-sidebar-link',
        'is-active' => $active,
    ]) }}
>
    <i data-lucide="{{ $icon }}" class="nexo-sidebar-link-icon" aria-hidden="true"></i>
    <span class="nexo-sidebar-link-label">{{ $slot }}</span>

    @if((int) $badge > 0)
        <span class="nexo-sidebar-badge" aria-label="{{ (int) $badge }} alertas pendentes" data-sidebar-badge>
            {{ (int) $badge > 99 ? '99+' : (int) $badge }}
        </span>
    @endif
</a>
