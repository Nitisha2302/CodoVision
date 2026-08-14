<div>
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">Users (Team members)</h2>
            <p class="crm-muted" style="margin:4px 0 0;">
                People who log into CRM. Assign each person a role and a team.
            </p>
        </div>
        <a class="crm-btn crm-btn-secondary" href="{{ route('crm.teams.index') }}">Manage teams</a>
    </div>

    <div class="crm-card" style="margin-bottom:14px;">
        <strong>Users vs Teams</strong>
        <p class="crm-muted" style="margin:6px 0 0;">
            <strong>Users</strong> = individual logins (Admin, Manager, Executive) with email/password and permissions.<br>
            <strong>Teams</strong> = groups that users belong to (e.g. Sales Core). Managers see leads for their team; executives usually see only assigned leads.
        </p>
    </div>

    <div class="crm-grid crm-grid-2">
        <div class="crm-card">
            <h3 style="margin-top:0;">{{ $isEditing ? 'Edit user' : 'Create user' }}</h3>
            <form wire:submit="save">
                <div class="crm-field">
                    <label class="crm-label">Name *</label>
                    <input class="crm-input" wire:model="name">
                    @include('crm::partials.field-error', ['name' => 'name'])
                </div>
                <div class="crm-field">
                    <label class="crm-label">Email *</label>
                    <input class="crm-input" type="email" wire:model="email">
                    @include('crm::partials.field-error', ['name' => 'email'])
                </div>
                <div class="crm-field">
                    <label class="crm-label">{{ $isEditing ? 'Password (leave blank to keep current)' : 'Password *' }}</label>
                    <input class="crm-input" type="password" wire:model="password" autocomplete="new-password">
                    @include('crm::partials.field-error', ['name' => 'password'])
                </div>
                <div class="crm-field">
                    <label class="crm-label">Phone</label>
                    <input class="crm-input" wire:model="phone">
                    @include('crm::partials.field-error', ['name' => 'phone'])
                </div>
                <div class="crm-field">
                    <label class="crm-label">Team</label>
                    <select class="crm-select" wire:model="team_id">
                        <option value="">None</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @include('crm::partials.field-error', ['name' => 'team_id'])
                    <div class="crm-muted" style="font-size:12px;margin-top:4px;">Create teams under Teams, then assign users here.</div>
                </div>
                <div class="crm-field">
                    <label class="crm-label">Role *</label>
                    <select class="crm-select" wire:model="role">
                        @foreach($roles as $roleName)
                            <option value="{{ $roleName }}">{{ $roleName }}</option>
                        @endforeach
                    </select>
                    @include('crm::partials.field-error', ['name' => 'role'])
                </div>
                <label class="crm-muted" style="display:flex;gap:8px;margin-bottom:12px;">
                    <input type="checkbox" wire:model="is_active"> Active
                </label>
                <div class="crm-actions">
                    <button class="crm-btn" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save changes' : 'Create user' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    @if($isEditing)
                        <button class="crm-btn crm-btn-secondary" type="button" wire:click="cancelEdit">Cancel</button>
                    @endif
                </div>
            </form>
        </div>

        <div class="crm-card">
            <h3 style="margin-top:0;">All users</h3>
            <table class="crm-table">
                <thead><tr><th>Name</th><th>Role</th><th>Team</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($users as $user)
                    <tr @class(['crm-row-active' => $editingId === $user->id])>
                        <td>{{ $user->name }}<div class="crm-muted">{{ $user->email }}</div></td>
                        <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                        <td>{{ $user->team?->name ?? '—' }}</td>
                        <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <div class="crm-actions">
                                <button class="crm-btn crm-btn-secondary" type="button" wire:click="edit({{ $user->id }})">Edit</button>
                                <button class="crm-btn crm-btn-secondary" type="button" wire:click="toggleActive({{ $user->id }})">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="crm-muted">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
