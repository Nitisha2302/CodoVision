<div>
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">Leads</h2>
            <p class="crm-muted" style="margin:4px 0 0;">Search, filter, and manage your pipeline.</p>
        </div>
        <div class="crm-actions">
            <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.kanban') }}">Kanban</a>
            @if($canExport)
                <button class="crm-btn crm-btn-secondary" wire:click="export" type="button">Export CSV</button>
            @endif
            <a class="crm-btn" href="{{ route('crm.leads.create') }}">+ New Lead</a>
        </div>
    </div>

    <div class="crm-card" style="margin-bottom:14px;">
        <div class="crm-grid crm-grid-4">
            <div class="crm-field" style="margin:0;"><label class="crm-label">Search</label><input class="crm-input" wire:model.live.debounce.300ms="search" placeholder="Name, email, phone, company, code"></div>
            <div class="crm-field" style="margin:0;"><label class="crm-label">Status</label>
                <select class="crm-select" wire:model.live="status"><option value="">All</option>@foreach($statuses as $s)<option value="{{ $s->slug }}">{{ $s->name }}</option>@endforeach</select>
            </div>
            <div class="crm-field" style="margin:0;"><label class="crm-label">Source</label>
                <select class="crm-select" wire:model.live="source"><option value="">All</option>@foreach($sources as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
            </div>
            <div class="crm-field" style="margin:0;"><label class="crm-label">Priority</label>
                <select class="crm-select" wire:model.live="priority"><option value="">All</option><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select>
            </div>
        </div>
    </div>

    <div class="crm-card">
        <table class="crm-table">
            <thead>
            <tr><th>Code</th><th>Title</th><th>Contact</th><th>Company</th><th>Status</th><th>Follow-up</th><th>Assignee</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($leads as $lead)
                <tr @class(['crm-row-followup-'.$lead->followUpState() => (bool) $lead->followUpState()])>
                    <td>{{ $lead->lead_code }}</td>
                    <td>{{ $lead->title }}</td>
                    <td>{{ $lead->primaryContact?->fullName() }}<div class="crm-muted">{{ $lead->primaryContact?->email }}</div></td>
                    <td>{{ $lead->company?->name ?? '—' }}</td>
                    <td><span class="crm-badge">{{ $lead->status?->name }}</span></td>
                    <td>
                        @if($lead->next_follow_up_at)
                            <span class="crm-badge crm-badge-{{ $lead->followUpState() }}">{{ $lead->next_follow_up_at->format('d M, h:i A') }}</span>
                        @else
                            <span class="crm-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $lead->assignee?->name ?? '—' }}</td>
                    <td><a href="{{ route('crm.leads.show', $lead) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="crm-muted">No leads match your filters.</td></tr>
            @endforelse
            </tbody>
        </table>
        @if ($leads->hasPages())
            <div class="crm-actions" style="margin-top:12px;align-items:center;">
                <button type="button" class="crm-btn crm-btn-secondary" wire:click="previousPage" @disabled($leads->onFirstPage())>Previous</button>
                <span class="crm-muted">Page {{ $leads->currentPage() }} of {{ $leads->lastPage() }}</span>
                <button type="button" class="crm-btn crm-btn-secondary" wire:click="nextPage" @disabled(!$leads->hasMorePages())>Next</button>
            </div>
        @endif
    </div>
</div>
