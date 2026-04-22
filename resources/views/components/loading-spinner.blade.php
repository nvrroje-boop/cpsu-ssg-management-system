<div class="spinner-overlay" {{ !($show ?? false) ? 'hidden' : '' }}>
    <div class="spinner" role="status" aria-label="{{ $label ?? 'Loading' }}">
        <div class="spinner-ring"></div>
        <p class="spinner-text">{{ $label ?? 'Loading...' }}</p>
    </div>
</div>

<style>
    .spinner-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        backdrop-filter: blur(4px);
    }

    .spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }

    .spinner-ring {
        width: 3rem;
        height: 3rem;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top-color: var(--ssg-500);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .spinner-text {
        color: var(--cream);
        font-size: 1rem;
        font-weight: 500;
        margin: 0;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
