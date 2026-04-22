@props([
    'type' => 'info',
    'dismissible' => true,
])

<div
    {{ $attributes->class([
        'portal-alert',
        'portal-alert--success' => $type === 'success',
        'portal-alert--error' => $type === 'error',
        'portal-alert--warning' => $type === 'warning',
        'portal-alert--info' => $type === 'info' || ! in_array($type, ['success', 'error', 'warning'], true),
    ]) }}
    data-alert
    role="status"
>
    <div class="portal-alert__content">{{ $slot }}</div>

    @if ($dismissible)
        <button type="button" class="portal-alert__close" aria-label="Dismiss notification" data-alert-close>
            <span aria-hidden="true">&times;</span>
        </button>
    @endif
</div>
