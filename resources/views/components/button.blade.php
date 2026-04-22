@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $classes = [
        'portal-button',
        'portal-button--primary' => $variant === 'primary',
        'portal-button--secondary' => $variant === 'secondary',
        'portal-button--accent' => $variant === 'accent',
        'portal-button--ghost' => $variant === 'ghost',
        'portal-button--sm' => $size === 'sm',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
