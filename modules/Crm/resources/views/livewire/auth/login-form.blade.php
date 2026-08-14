<div class="crm-login-wrap">
    <div class="crm-card crm-login-card">
        <h1 style="margin:0 0 6px;">CodoVision CRM</h1>
        <p class="crm-muted" style="margin:0 0 18px;">Sign in to manage leads and pipeline.</p>

        <form wire:submit="login">
            <div class="crm-field">
                <label class="crm-label">Email</label>
                <input type="email" class="crm-input" wire:model="email" autocomplete="username">
                @include('crm::partials.field-error', ['name' => 'email'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Password</label>
                <input type="password" class="crm-input" wire:model="password" autocomplete="current-password">
                @include('crm::partials.field-error', ['name' => 'password'])
            </div>
            <label style="display:flex;gap:8px;align-items:center;margin-bottom:14px;color:var(--crm-muted);font-size:13px;">
                <input type="checkbox" wire:model="remember"> Remember me
            </label>
            <button class="crm-btn" type="submit" style="width:100%;" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Sign in</span>
                <span wire:loading wire:target="login">Signing in…</span>
            </button>
        </form>
    </div>
</div>
