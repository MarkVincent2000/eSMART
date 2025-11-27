@props([
    'href',
    'title',
    'badge' => null,
    'badgeClass' => 'badge bg-success',
])

@php
    $isActive = request()->is($href) || request()->is($href . '/*');
@endphp
<li class="nav-item">
    <a href="{{ route('index', ['any' => $href]) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
        <span>{{ $title }}</span>
        @if ($badge)
            <span class="{{ $badgeClass }}">{{ $badge }}</span>
        @endif
    </a>
</li>


