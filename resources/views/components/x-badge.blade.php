@props([
    'type' => 'default', // e.g. success, danger, warning, info, default
    'class' => ''
])
<span {{ $attributes->merge(['class' => "x-badge x-badge--$type $class"]) }}>
    {{ $slot }}
</span>
