{{--
Toast Notification Component

A reusable Blade component for creating toast notifications using Toastify JS.
Based on the Display Position examples from ui-notifications.blade.php

Usage:
<x-toast-notification text="Your message here" gravity="top" position="right" className="success">
    Button Text
</x-toast-notification>

Props:
- text: The toast notification message (default: 'This is a Toast Notification')
- gravity: Vertical position - 'top' or 'bottom' (default: 'top')
- position: Horizontal position - 'left', 'center', or 'right' (default: 'right')
- duration: Display duration in milliseconds (default: 3000, use -1 for persistent)
- className: Toast style - 'primary', 'success', 'warning', 'danger' (default: 'primary')
- close: Show close button (default: true)
- offset: Add offset to position (default: false)
- style: Apply custom style (default: false)
- tag: HTML tag to use - 'button', 'div', 'a', etc. (default: 'button')
- class: Additional CSS classes (default: 'btn btn-light w-xs')
- type: Button type when tag is 'button' (default: 'button')
--}}
@props([
    'text' => 'This is a Toast Notification',
    'gravity' => 'top', // 'top' or 'bottom'
    'position' => 'right', // 'left', 'center', or 'right'
    'duration' => 3000, // milliseconds, -1 for persistent
    'className' => 'primary', // 'primary', 'success', 'warning', 'danger'
    'close' => true, // show close button
    'offset' => false, // offset position
    'style' => false, // custom style
    'tag' => 'button', // 'button' or 'div' or custom tag
    'class' => 'btn btn-light w-xs', // additional CSS classes
    'type' => 'button', // button type (only for button tag)
])
@php
    // Merge class attributes
    $classes = $attributes->get('class', '');
    $mergedClasses = trim($class . ' ' . $classes);

    // Build toast data attributes
    $toastAttributes = '';
    $toastAttributes .= ' data-toast';
    $toastAttributes .= ' data-toast-text="' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '"';
    $toastAttributes .= ' data-toast-gravity="' . htmlspecialchars($gravity, ENT_QUOTES, 'UTF-8') . '"';
    $toastAttributes .= ' data-toast-position="' . htmlspecialchars($position, ENT_QUOTES, 'UTF-8') . '"';
    $toastAttributes .= ' data-toast-duration="' . htmlspecialchars($duration, ENT_QUOTES, 'UTF-8') . '"';

    if ($className) {
        $toastAttributes .= ' data-toast-className="' . htmlspecialchars($className, ENT_QUOTES, 'UTF-8') . '"';
    }

    if ($close) {
        $toastAttributes .= ' data-toast-close="close"';
    }

    if ($offset) {
        $toastAttributes .= ' data-toast-offset';
    }

    if ($style) {
        $toastAttributes .= ' data-toast-style="style"';
    }
@endphp

<{{ $tag }} 
    {{ $attributes->merge(['class' => $mergedClasses]) }}
    @if($tag === 'button' && !$attributes->has('type')) 
        type="{{ $type }}" 
    @endif
    {!! $toastAttributes !!}
>
    {{ $slot }}
</{{ $tag }}>
