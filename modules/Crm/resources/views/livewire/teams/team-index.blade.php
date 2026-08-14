<div>
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">Teams</h2>
            <p class="crm-muted" style="margin:4px 0 0;">
                Groups for sales people. Users are assigned to a team from the Users page.
            </p>
        </div>
        <a class="crm-btn crm-btn-secondary" href="{{ route('crm.users.index') }}">Manage users</a>
    </div>

    <div class="crm-card" style="margin-bottom:14px;">
        <strong>Teams vs Users</strong>
        <p class="crm-muted" style="margin:6px 0 0;">
            <strong>Teams</strong> = organization groups (Sales Core, Enterprise, etc.). Used for pipeline visibility and round-robin assignment.<br>
            <strong>Users</strong> = the actual people. Edit a user’s team/role under Users.
        </p>
    </div>

    <div class="crm-grid crm-grid-2">
        <div class="crm-card">
            <h3 style="margin-top:0;">{{ $isEditing ? 'Edit team' : 'Create team' }}</h3>
            <form wire:submit="save">
                <div class="crm-field">
                    <label class="crm-label">Name *</label>
                    <input class="crm-input" wire:model="name" placeholder="e.g. Enterprise Sales">
                    @include('crm::partials.field-error', ['name' => 'name'])
                </div>
                <div class="crm-field">
                    <label class="crm-label">Description</label>
                    <textarea class="crm-textarea" wire:model="description" placeholder="Optional team description"></textarea>
                    @include('crm::partials.field-error', ['name' => 'description'])
                </div>
                @if($isEditing)
                    <label class="crm-muted" style="display:flex;gap:8px;margin-bottom:12px;">
                        <input type="checkbox" wire:model="is_active"> Active
                    </label>
                @endif
                <div class="crm-actions">
                    <button class="crm-btn" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save changes' : 'Create team' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    @if($isEditing)
                        <button class="crm-btn crm-btn-secondary" type="button" wire:click="cancelEdit">Cancel</button>
                    @endif
                </div>
            </form>
        </div>
        <div class="crm-card">
            <h3 style="margin-top:0;">All teams</h3>
            <table class="crm-table">
                <thead><tr><th>Name</th><th>Members</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($teams as $team)
                    <tr @class(['crm-row-active' => $editingId === $team->id])>
                        <td>
                            {{ $team->name }}
                            <div class="crm-muted">{{ $team->description }}</div>
                            @if($team->users->isNotEmpty())
                                <div class="crm-muted" style="margin-top:6px;font-size:12px;">
                                    {{ $team->users->pluck('name')->join(', ') }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $team->users_count }}</td>
                        <td>{{ $team->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <button class="crm-btn crm-btn-secondary" type="button" wire:click="edit({{ $team->id }})">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="crm-muted">No teams yet. Create your first team.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
