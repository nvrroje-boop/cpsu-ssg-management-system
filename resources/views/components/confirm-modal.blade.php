<div class="modal-overlay" id="confirmModal" {{ !($show ?? false) ? 'hidden' : '' }}>
    <div class="modal modal-confirm" role="dialog" aria-labelledby="confirmTitle" aria-modal="true">
        <div class="modal-content">
            <h2 id="confirmTitle" class="modal-title">{{ $title ?? 'Confirm Action' }}</h2>
            <p class="modal-text">{{ $message ?? '' }}</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="$dispatch('closeConfirm')">
                {{ $cancelLabel ?? 'Cancel' }}
            </button>
            <button type="button" class="btn btn-primary" @click="$dispatch(@js($actionName ?? 'confirm'))">
                {{ $confirmLabel ?? 'Confirm' }}
            </button>
        </div>
    </div>
</div>
