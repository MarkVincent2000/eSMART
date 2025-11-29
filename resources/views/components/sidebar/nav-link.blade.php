@props([
    'href',
    'icon' => null,
    'title' => null,
    'translation' => null, // Translation key for @lang()
    'active' => null, // Optional: manually set active state, otherwise auto-detected
    'route' => false, // If true, uses route() helper, otherwise direct href
])

@php
    // Determine if link should be active
    if ($active !== null) {
        $isActive = $active;
    } else {
        // Auto-detect active state based on href
        if ($route) {
            $isActive = request()->routeIs($href) || request()->routeIs($href . '.*');
        } else {
            $isActive = request()->is($href) || request()->is($href . '/*');
        }
    }
    
    // Build href attribute
    if ($route) {
        $hrefValue = route($href);
    } else {
        // Check if it's a route name or direct URL
        $hrefValue = str_contains($href, '://') || str_starts_with($href, '/') 
            ? $href 
            : route('index', ['any' => $href]);
    }
    
@endphp

<li class="nav-item">
    <a class="nav-link menu-link{{ $isActive ? ' active' : '' }}" href="{{ $hrefValue }}">
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        <span>
            @if ($translation)
                @lang($translation)
            @elseif ($title)
                {{ $title }}
            @else
                {{ $slot }}
            @endif
        </span>
    </a>
</li>

