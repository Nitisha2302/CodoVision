<div>
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">Pipeline</h2>
            <p class="crm-muted" style="margin:4px 0 0;">Move leads across stages. Transitions are logged.</p>
        </div>
        <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.index') }}">List view</a>
    </div>

    <div class="crm-kanban">
        @foreach($statuses as $status)
            <div class="crm-col">
                <h4>
                    <span>{{ $status->name }}</span>
                    <span class="crm-muted">{{ $status->leads->count() }}</span>
                </h4>
                @forelse($status->leads as $lead)
                    <div class="crm-lead-card">
                        <h5><a href="{{ route('crm.leads.show', $lead) }}">{{ $lead->lead_code }}</a></h5>
                        <p>{{ $lead->primaryContact?->fullName() }}</p>
                        <p>{{ $lead->company?->name }}</p>
                        <p style="margin-top:6px;">{{ $lead->assignee?->name ?? 'Unassigned' }}</p>
                        <div class="crm-field" style="margin-top:8px;margin-bottom:0;">
                            <select class="crm-select" wire:change="moveLead({{ $lead->id }}, $event.target.value)">
                                <option value="">Move to…</option>
                                @foreach($statuses as $option)
                                    @if($option->id !== $status->id)
                                        <option value="{{ $option->slug }}">{{ $option->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                @empty
                    <p class="crm-muted" style="font-size:12px;margin:8px 0;">No leads</p>
                @endforelse
            </div>
        @endforeach
    </div>

    @if($showTransitionModal)
        <div class="crm-card" style="margin-top:16px;border-color:rgba(99,102,241,.4);">
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
