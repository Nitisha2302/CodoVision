<div class="crm-pipeline-page">
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">Pipeline</h2>
            <p class="crm-muted" style="margin:4px 0 0;">Drag stages on mobile · move leads between columns on web.</p>
        </div>
        <div class="crm-actions">
            <a class="crm-btn" href="{{ route('crm.leads.create') }}">+ New Lead</a>
            <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.index') }}">List view</a>
        </div>
    </div>

    <div class="crm-pipeline-summary">
        @foreach($statuses as $status)
            <div class="crm-pipeline-chip" style="--stage: {{ $status->color ?: '#6366f1' }}">
                <span class="crm-pipeline-chip-dot"></span>
                <span>{{ $status->name }}</span>
                <strong>{{ $status->leads->count() }}</strong>
            </div>
        @endforeach
    </div>

    <div class="crm-kanban-hint crm-muted">Swipe sideways to browse stages</div>

    <div class="crm-kanban">
        @foreach($statuses as $status)
            <section class="crm-col" style="--stage: {{ $status->color ?: '#6366f1' }}">
                <header class="crm-col-head">
                    <div>
                        <h4>{{ $status->name }}</h4>
                        <p class="crm-muted">{{ $status->leads->count() }} lead{{ $status->leads->count() === 1 ? '' : 's' }}</p>
                    </div>
                    <span class="crm-col-count">{{ $status->leads->count() }}</span>
                </header>

                <div class="crm-col-body">
                    @forelse($status->leads as $lead)
                        <article class="crm-lead-card">
                            <div class="crm-lead-card-top">
                                <a class="crm-lead-code" href="{{ route('crm.leads.show', $lead) }}">{{ $lead->lead_code }}</a>
                                <span class="crm-badge crm-badge-{{ $lead->priority === 'urgent' ? 'overdue' : ($lead->priority === 'high' ? 'today' : 'scheduled') }}">{{ ucfirst($lead->priority ?: 'medium') }}</span>
                            </div>
                            <h5 class="crm-lead-card-title">
                                <a href="{{ route('crm.leads.show', $lead) }}">{{ $lead->title ?: ($lead->primaryContact?->fullName() ?: 'Untitled lead') }}</a>
                            </h5>
                            <div class="crm-lead-card-meta">
                                <div><span class="crm-muted">Contact</span> {{ $lead->primaryContact?->fullName() ?: '—' }}</div>
                                <div><span class="crm-muted">Company</span> {{ $lead->company?->name ?: '—' }}</div>
                                <div><span class="crm-muted">Owner</span> {{ $lead->assignee?->name ?? 'Unassigned' }}</div>
                                @if($lead->source)
                                    <div><span class="crm-muted">Source</span> {{ $lead->source->name }}</div>
                                @endif
                                @if($lead->expected_value)
                                    <div><span class="crm-muted">Value</span> {{ $lead->currency ?: 'USD' }} {{ number_format((float) $lead->expected_value, 0) }}</div>
                                @endif
                            </div>
                            <div class="crm-lead-card-actions">
                                <a class="crm-btn crm-btn-secondary crm-btn-sm" href="{{ route('crm.leads.show', $lead) }}">Open</a>
                                <select class="crm-select crm-lead-move" wire:change="moveLead({{ $lead->id }}, $event.target.value)" aria-label="Move lead">
                                    <option value="">Move…</option>
                                    @foreach($statuses as $option)
                                        @if($option->id !== $status->id)
                                            <option value="{{ $option->slug }}">{{ $option->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </article>
                    @empty
                        <div class="crm-col-empty">No leads in this stage</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    @if($showTransitionModal)
        <div class="crm-card crm-transition-modal">
            <h3 style="margin-top:0;">Confirm stage change → {{ strtoupper($targetStatusSlug) }}</h3>
            <form wire:submit="confirmTransition">
                @if($targetStatusSlug === 'lost')
                    <div class="crm-field">
                        <label class="crm-label">Lost reason *</label>
                        <select class="crm-select" wire:model="lost_reason_id">
                            <option value="">Select reason</option>
                            @foreach($lostReasons as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                            @endforeach
                        </select>
                        @include('crm::partials.field-error', ['name' => 'lost_reason_id'])
                    </div>
                    <div class="crm-field">
                        <label class="crm-label">Notes</label>
                        <textarea class="crm-textarea" wire:model="lost_notes"></textarea>
                        @include('crm::partials.field-error', ['name' => 'lost_notes'])
                    </div>
                @endif
                @if($targetStatusSlug === 'won')
                    <div class="crm-grid crm-grid-2">
                        <div class="crm-field">
                            <label class="crm-label">Won amount *</label>
                            <input class="crm-input" type="number" step="0.01" wire:model="won_amount">
                            @include('crm::partials.field-error', ['name' => 'won_amount'])
                        </div>
                        <div class="crm-field">
                            <label class="crm-label">Currency *</label>
                            <input class="crm-input" wire:model="won_currency" maxlength="3">
                            @include('crm::partials.field-error', ['name' => 'won_currency'])
                        </div>
                        @include('crm::partials.datetime-picker', [
                            'model' => 'won_closing_date',
                            'type' => 'date',
                            'label' => 'Closing date',
                            'required' => true,
                        ])
                        <div class="crm-field"><label class="crm-label">Service</label><input class="crm-input" wire:model="won_service"></div>
                        <div class="crm-field" style="grid-column:1/-1;"><label class="crm-label">Project type</label><input class="crm-input" wire:model="won_project_type"></div>
                    </div>
                @endif
                <div class="crm-actions">
                    <button class="crm-btn" type="submit" wire:loading.attr="disabled">Confirm</button>
                    <button class="crm-btn crm-btn-secondary" type="button" wire:click="resetTransition">Cancel</button>
                </div>
            </form>
        </div>
    @endif
</div>
