@props([
    'id' => null,
    'title' => null,
    'show' => false,
    'maxWidth' => '2xl',
    'class' => ''
])
<div
    x-data="{ show: @js($show) }"
    x-show="show"
    x-on:close.stop="show = false"
    id="{{ $id }}"
    class="x-modal x-modal--{{ $maxWidth }} {{ $class }}"
    style="display: none;"
    {{ $attributes }}
>
    <div class="x-modal__overlay" x-on:click="show = false"></div>
    <div class="x-modal__content">
        <div class="x-modal__header">
            <h2 class="x-modal__title">{{ $title }}</h2>
            <button type="button" class="x-modal__close" x-on:click="show = false" aria-label="Close">&times;</button>
        </div>
        <div class="x-modal__body">
            {{ $slot }}
        </div>
    </div>
</div>
