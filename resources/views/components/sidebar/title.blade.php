@props([
    'title',
    'icon' => null,
])

<li class="menu-title">
    @if ($icon)
        <i class="{{ $icon }}"></i>
    @endif
    <span>{{ $title }}</span>
</li>


