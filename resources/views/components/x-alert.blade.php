@props([
    'type' => 'info', // info, success, warning, danger
    'dismissible' => false,
    'class' => ''
])
<div {{ $attributes->merge(['class' => "x-alert x-alert--$type $class"]) }} role="alert">
    <span class="x-alert__icon">
        @if($type === 'success')
            ✓
        @elseif($type === 'danger')
            !
        @elseif($type === 'warning')
            !
        @else
            i
        @endif
    </span>
    <span class="x-alert__message">{{ $slot }}</span>
    @if($dismissible)
        <button type="button" class="x-alert__close" aria-label="Close" onclick="this.parentElement.style.display='none';">&times;</button>
    @endif
</div>
