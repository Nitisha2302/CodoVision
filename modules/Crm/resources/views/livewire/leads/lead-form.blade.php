<div>
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">{{ $leadId ? 'Edit Lead' : 'Create Lead' }}</h2>
            <p class="crm-muted" style="margin:4px 0 0;">Capture contact, company, and opportunity details.</p>
        </div>
        <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.index') }}">Back</a>
    </div>

    @if($duplicateWarning)
        <div class="crm-alert crm-alert-error">{{ $duplicateWarning }}</div>
    @endif

    <form wire:submit="save" class="crm-card">
        <h3 style="margin-top:0;">Contact</h3>
        <div class="crm-grid crm-grid-2">
            <div class="crm-field">
                <label class="crm-label">First name *</label>
                <input class="crm-input" wire:model="contact_first_name">
                @include('crm::partials.field-error', ['name' => 'contact_first_name'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Last name</label>
                <input class="crm-input" wire:model="contact_last_name">
                @include('crm::partials.field-error', ['name' => 'contact_last_name'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Email</label>
                <input class="crm-input" type="email" wire:model="contact_email">
                @include('crm::partials.field-error', ['name' => 'contact_email'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Phone</label>
                <input class="crm-input" wire:model="contact_phone">
                @include('crm::partials.field-error', ['name' => 'contact_phone'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Job title</label>
                <input class="crm-input" wire:model="contact_job_title">
            </div>
            <div class="crm-field" style="display:flex;align-items:end;">
                <label class="crm-muted"><input type="checkbox" wire:model="is_decision_maker"> Decision maker</label>
            </div>
        </div>

        <h3>Company</h3>
        <div class="crm-grid crm-grid-2">
            <div class="crm-field"><label class="crm-label">Company</label><input class="crm-input" wire:model="company_name"></div>
            <div class="crm-field"><label class="crm-label">Website</label><input class="crm-input" wire:model="company_website"></div>
            <div class="crm-field"><label class="crm-label">Country</label><input class="crm-input" wire:model="company_country"></div>
            <div class="crm-field"><label class="crm-label">City</label><input class="crm-input" wire:model="company_city"></div>
        </div>

        <h3>Opportunity</h3>
        <div class="crm-grid crm-grid-2">
            <div class="crm-field"><label class="crm-label">Title</label><input class="crm-input" wire:model="title"></div>
            <div class="crm-field"><label class="crm-label">Service interested</label><input class="crm-input" wire:model="service_interested"></div>
            <div class="crm-field crm-source-field">
                <label class="crm-label">Source</label>
                <select class="crm-select" wire:model.live="lead_source_id">
                    <option value="">Select source</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                    <option value="__custom__">Other — type custom source</option>
                </select>
                <input
                    class="crm-input"
                    style="margin-top:8px;"
                    list="crm-lead-source-options"
                    wire:model.live.debounce.300ms="lead_source_name"
                    placeholder="Type source if not in list (e.g. Upwork, Freelance, Partner)"
                    autocomplete="off"
                >
                <datalist id="crm-lead-source-options">
                    @foreach($sources as $source)
                        <option value="{{ $source->name }}"></option>
                    @endforeach
                </datalist>
                <p class="crm-muted" style="margin:6px 0 0;font-size:12px;">Pick from the list or type a new source — it will be saved for next time.</p>
                @include('crm::partials.field-error', ['name' => 'lead_source_name'])
            </div>
            <div class="crm-field"><label class="crm-label">Campaign</label><input class="crm-input" wire:model="campaign"></div>
            <div class="crm-field">
                <label class="crm-label">Priority *</label>
                <select class="crm-select" wire:model="priority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                @include('crm::partials.field-error', ['name' => 'priority'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Status *</label>
                <select class="crm-select" wire:model="status_slug">
                    @foreach($statuses as $status)<option value="{{ $status->slug }}">{{ $status->name }}</option>@endforeach
                </select>
                @include('crm::partials.field-error', ['name' => 'status_slug'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Lead score</label>
                <input class="crm-input" type="number" min="0" max="100" wire:model="lead_score">
                @include('crm::partials.field-error', ['name' => 'lead_score'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Probability %</label>
                <input class="crm-input" type="number" min="0" max="100" wire:model="probability">
                @include('crm::partials.field-error', ['name' => 'probability'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Budget</label>
                <input class="crm-input" type="number" step="0.01" wire:model="budget">
                @include('crm::partials.field-error', ['name' => 'budget'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Expected value</label>
                <input class="crm-input" type="number" step="0.01" wire:model="expected_value">
                @include('crm::partials.field-error', ['name' => 'expected_value'])
            </div>
            <div class="crm-field">
                <label class="crm-label">Currency *</label>
                <input class="crm-input" wire:model="currency" maxlength="3">
                @include('crm::partials.field-error', ['name' => 'currency'])
            </div>
            @include('crm::partials.datetime-picker', [
                'model' => 'expected_closing_date',
                'type' => 'date',
                'label' => 'Expected closing',
            ])
            @if($canReassign)
                <div class="crm-field">
                    <label class="crm-label">Assignee</label>
                    <select class="crm-select" wire:model="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                    </select>
                    @include('crm::partials.field-error', ['name' => 'assigned_to'])
                </div>
                <div class="crm-field">
                    <label class="crm-label">Team</label>
                    <select class="crm-select" wire:model="team_id">
                        <option value="">None</option>
                        @foreach($teams as $team)<option value="{{ $team->id }}">{{ $team->name }}</option>@endforeach
                    </select>
                    @include('crm::partials.field-error', ['name' => 'team_id'])
                </div>
            @endif
        </div>

        <h3>Follow-up</h3>
        <div class="crm-grid crm-grid-2">
            @include('crm::partials.datetime-picker', [
                'model' => 'next_follow_up_at',
                'type' => 'datetime-local',
                'label' => 'Next follow-up date & time',
            ])
            <div class="crm-field">
                <label class="crm-label">Follow-up note</label>
                <input class="crm-input" wire:model="follow_up_note" placeholder="Call back / send proposal / demo">
                @include('crm::partials.field-error', ['name' => 'follow_up_note'])
            </div>
        </div>

        <h3>Business details</h3>
        <div class="crm-grid crm-grid-2">
            <div class="crm-field"><label class="crm-label">Existing site/app</label><input class="crm-input" wire:model="existing_site_app"></div>
            <div class="crm-field"><label class="crm-label">Timeline</label><input class="crm-input" wire:model="timeline"></div>
            <div class="crm-field"><label class="crm-label">Decision maker</label><input class="crm-input" wire:model="decision_maker"></div>
            <div class="crm-field"><label class="crm-label">Competitor</label><input class="crm-input" wire:model="competitor"></div>
            <div class="crm-field" style="grid-column:1/-1;"><label class="crm-label">Tech requirements</label><textarea class="crm-textarea" wire:model="tech_requirements"></textarea></div>
            <div class="crm-field" style="grid-column:1/-1;"><label class="crm-label">Notes</label><textarea class="crm-textarea" wire:model="notes"></textarea></div>
        </div>

        <button class="crm-btn" type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">{{ $leadId ? 'Save changes' : ($force_create ? 'Force create lead' : 'Create lead') }}</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </form>
</div>
