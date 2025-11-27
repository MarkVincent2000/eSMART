@props([
    'id',
    'title',
    'icon' => null,
    'badge' => null,
    'badgeClass' => 'badge badge-pill bg-danger',
    'expanded' => false,
    'active' => false,
])

@php
    $isActive = $active;
    $isExpanded = ($expanded || $isActive) ? 'true' : 'false';
    $linkClasses = 'nav-link menu-link' . ($isActive ? ' active' : '');
    $menuClasses = 'collapse menu-dropdown' . ($isActive ? ' show' : '');
@endphp

<li class="nav-item">
    <a class="{{ $linkClasses }}" href="#{{ $id }}" data-bs-toggle="collapse" role="button"
        aria-expanded="{{ $isExpanded }}" aria-controls="{{ $id }}">
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        <span>{{ $title }}</span>
        @if ($badge)
            <span class="{{ $badgeClass }}">{{ $badge }}</span>
        @endif
    </a>
    <div class="{{ $menuClasses }}" id="{{ $id }}">
        {{ $slot }}
    </div>
</li>


