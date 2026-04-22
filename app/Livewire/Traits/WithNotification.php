<?php

namespace App\Livewire\Traits;

use Livewire\Attributes\Computed;

/**
 * Provides notification flash messages for Livewire components
 */
trait WithNotification
{
    /**
     * Dispatch a success notification
     */
    public function notifySuccess(string $message, array $payload = []): void
    {
        $this->dispatch('notify', [...$payload, 'type' => 'success', 'message' => $message]);
    }

    /**
     * Dispatch an error notification
     */
    public function notifyError(string $message, array $payload = []): void
    {
        $this->dispatch('notify', [...$payload, 'type' => 'error', 'message' => $message]);
    }

    /**
     * Dispatch a warning notification
     */
    public function notifyWarning(string $message, array $payload = []): void
    {
        $this->dispatch('notify', [...$payload, 'type' => 'warning', 'message' => $message]);
    }

    /**
     * Dispatch an info notification
     */
    public function notifyInfo(string $message, array $payload = []): void
    {
        $this->dispatch('notify', [...$payload, 'type' => 'info', 'message' => $message]);
    }

    /**
     * Ask for confirmation before proceeding
     */
    public function confirm(string $title, string $message, string $actionName = 'confirm'): void
    {
        $this->dispatch('confirm', title: $title, message: $message, actionName: $actionName);
    }
}
