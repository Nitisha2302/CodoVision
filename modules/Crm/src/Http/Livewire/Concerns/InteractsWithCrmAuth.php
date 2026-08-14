<?php

namespace Codovision\Crm\Http\Livewire\Concerns;

use Codovision\Crm\Models\CrmUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait InteractsWithCrmAuth
{
    public ?string $crmFlashSuccess = null;
    public ?string $crmFlashError = null;

    public function bootInteractsWithCrmAuth(): void
    {
        Auth::shouldUse('crm');
    }

    protected function crmUser(): CrmUser
    {
        Auth::shouldUse('crm');

        /** @var CrmUser|null $user */
        $user = Auth::guard('crm')->user();

        if (!$user || !$user->is_active) {
            throw new AuthorizationException('Your CRM session has expired. Please sign in again.');
        }

        return $user;
    }

    protected function crmAllows(string $ability, mixed $arguments = []): bool
    {
        return Gate::forUser($this->crmUser())->allows($ability, $arguments);
    }

    protected function crmAuthorize(string $ability, mixed $arguments = []): void
    {
        if (!$this->crmAllows($ability, $arguments)) {
            throw new AuthorizationException('You are not allowed to perform this action.');
        }
    }

    protected function crmFlashSuccess(string $message): void
    {
        $this->crmFlashSuccess = $message;
        $this->crmFlashError = null;
        session()->flash('crm_success', $message);
    }

    protected function crmFlashError(string $message): void
    {
        $this->crmFlashError = $message;
        $this->crmFlashSuccess = null;
        session()->flash('crm_error', $message);
    }

    protected function clearCrmFlash(): void
    {
        $this->crmFlashSuccess = null;
        $this->crmFlashError = null;
    }
}
