@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'default',
])

@php
    $bodyClass = match ($padding) {
        'flush' => 'portal-card__body--flush',
        'compact' => 'portal-card__body--compact',
        default => null,
    };
@endphp

<section {{ $attributes->class('portal-card') }}>
    @if ($title || $subtitle || isset($actions))
        <header class="portal-card__header">
            <div class="portal-card__heading">
                @if ($title)
                    <h2 class="portal-card__title">{{ $title }}</h2>
                @endif

                @if ($subtitle)
                    <p class="portal-card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="portal-card__actions">
                    {{ $actions }}
                </div>
            @endisset
        </header>
    @endif

    <div @class(['portal-card__body', $bodyClass])>
        {{ $slot }}
    </div>
</section>
