<div class="toast-container" id="toastContainer"
    @notify.window="
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + $event.detail.type;
        toast.innerHTML = `
            <div class='toast-content'>
                <span>${$event.detail.message}</span>
            </div>
        `;
        document.getElementById('toastContainer').appendChild(toast);
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    "
>
</div>

<style>
    .toast-container {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 9999;
        pointer-events: none;
    }

    .toast {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        padding: 1.25rem;
        margin-top: 0.75rem;
        box-shadow: var(--shadow-lg);
        min-width: 16rem;
        max-width: 24rem;
        opacity: 0;
        transform: translateY(2rem);
        transition: all 0.3s ease;
        pointer-events: auto;
        animation: slideUp 0.3s ease forwards;
    }

    .toast.show {
        opacity: 1;
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--text-primary);
        font-size: 0.875rem;
        font-weight: 500;
    }

    .toast-success {
        border-color: var(--ssg-500);
        background: linear-gradient(135deg, rgba(62, 165, 94, 0.05), transparent);
    }

    .toast-success .toast-content::before {
        content: "✓";
        color: var(--ssg-600);
        font-weight: bold;
        font-size: 1.25rem;
    }

    .toast-error {
        border-color: #EF4444;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), transparent);
    }

    .toast-error .toast-content::before {
        content: "✕";
        color: #DC2626;
        font-weight: bold;
        font-size: 1.25rem;
    }

    .toast-warning {
        border-color: #F59E0B;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), transparent);
    }

    .toast-warning .toast-content::before {
        content: "⚠";
        color: #D97706;
        font-weight: bold;
        font-size: 1.25rem;
    }

    .toast-info {
        border-color: #3B82F6;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), transparent);
    }

    .toast-info .toast-content::before {
        content: "ℹ";
        color: #1D4ED8;
        font-weight: bold;
        font-size: 1.25rem;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(2rem);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 640px) {
        .toast-container {
            bottom: 1rem;
            right: 1rem;
            left: 1rem;
        }

        .toast {
            min-width: auto;
            max-width: 100%;
        }
    }
</style>
