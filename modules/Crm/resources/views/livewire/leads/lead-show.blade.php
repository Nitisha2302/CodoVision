<div>
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">{{ $lead->lead_code }} · {{ $lead->title }}</h2>
            <p class="crm-muted" style="margin:4px 0 0;">
                <span class="crm-badge">{{ $lead->status?->name }}</span>
                · {{ $lead->priority }} priority
            </p>
        </div>
        <div class="crm-actions">
            @if($canSendMail && $lead->primaryContact?->email)
                <a class="crm-btn" href="{{ route('crm.mailbox.index', ['compose' => 1, 'to' => $lead->primaryContact->email, 'subject' => 'Re: '.$lead->title, 'lead_id' => $lead->id]) }}">Email contact</a>
            @elseif($canMailbox)
                <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.index') }}">Mailbox</a>
            @endif
            <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.edit', $lead) }}">Edit</a>
            <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.kanban') }}">Pipeline</a>
            @if($canDelete)
                <button class="crm-btn crm-btn-danger" type="button" wire:click="deleteLead" wire:confirm="Delete this lead? This cannot be undone.">Delete</button>
            @endif
        </div>
    </div>

    <div class="crm-grid crm-grid-2">
        <div class="crm-card">
            <h3 style="margin-top:0;">Lead details</h3>
            <p><strong>Contact:</strong> {{ $lead->primaryContact?->fullName() }}</p>
            <p><strong>Email:</strong> {{ $lead->primaryContact?->email ?? '—' }}</p>
            <p><strong>Phone:</strong> {{ $lead->primaryContact?->phone ?? '—' }}</p>
            <p><strong>Company:</strong> {{ $lead->company?->name ?? '—' }}</p>
            <p><strong>Service:</strong> {{ $lead->service_interested ?? '—' }}</p>
            <p><strong>Source:</strong> {{ $lead->source?->name ?? '—' }}</p>
            <p><strong>Value:</strong> {{ $lead->currency }} {{ number_format((float)$lead->expected_value, 2) }} ({{ $lead->probability }}%)</p>
            <p><strong>Assignee:</strong> {{ $lead->assignee?->name ?? '—' }}</p>
            <p>
                <strong>Follow-up:</strong>
                @if($lead->next_follow_up_at)
                    <span class="crm-badge crm-badge-{{ $followUpState }}">{{ $lead->next_follow_up_at->format('d M Y, h:i A') }}</span>
                    @if($lead->follow_up_note)
                        <span class="crm-muted">— {{ $lead->follow_up_note }}</span>
                    @endif
                @else
                    <span class="crm-muted">Not scheduled</span>
                @endif
            </p>
            @if($lead->status?->is_lost)
                <p><strong>Lost reason:</strong> {{ $lead->lostReason?->name }} — {{ $lead->lost_notes }}</p>
            @endif
            @if($lead->status?->is_won)
                <p><strong>Won:</strong> {{ $lead->won_currency }} {{ number_format((float)$lead->won_amount, 2) }} on {{ optional($lead->won_closing_date)->toDateString() }}</p>
            @endif
            <p class="crm-muted">{{ $lead->notes }}</p>

            <form wire:submit="saveFollowUp" style="margin-top:16px;padding-top:14px;border-top:1px solid var(--crm-border);">
                <h4 style="margin:0 0 10px;">Schedule follow-up</h4>
                @include('crm::partials.datetime-picker', [
                    'model' => 'followUpAt',
                    'type' => 'datetime-local',
                    'label' => 'Date & time',
                    'required' => true,
                ])
                <div class="crm-field">
                    <label class="crm-label">Note</label>
                    <input class="crm-input" wire:model="followUpNote" placeholder="What to do next">
                    @include('crm::partials.field-error', ['name' => 'followUpNote'])
                </div>
                <div class="crm-actions">
                    <button class="crm-btn" type="submit">Save follow-up</button>
                    @if($lead->next_follow_up_at)
                        <button class="crm-btn crm-btn-secondary" type="button" wire:click="clearFollowUp">Clear</button>
                    @endif
                </div>
            </form>

            @if($canReassign)
                <form wire:submit="reassign" style="margin-top:16px;">
                    <label class="crm-label">Reassign</label>
                    <div class="crm-actions">
                        <select class="crm-select" wire:model="assignTo" style="max-width:240px;">
                            <option value="">Select user</option>
                            @foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                        </select>
                        <button class="crm-btn" type="submit">Assign</button>
                    </div>
                    @include('crm::partials.field-error', ['name' => 'assignTo'])
                </form>
            @endif
        </div>

        <div class="crm-card">
            <h3 style="margin-top:0;">Internal notes</h3>
            <form wire:submit="addNote">
                <textarea class="crm-textarea" wire:model="noteBody" placeholder="Add internal note. Use @name to mention."></textarea>
                @include('crm::partials.field-error', ['name' => 'noteBody'])
                <button class="crm-btn" type="submit" style="margin-top:8px;">Add note</button>
            </form>
            <ul class="crm-timeline" style="margin-top:16px;">
                @forelse($lead->notesRelation as $note)
                    <li>
                        <strong>{{ $note->author?->name }}</strong>
                        <span class="crm-muted">· {{ $note->created_at?->diffForHumans() }}</span>
                        <div>{{ $note->body }}</div>
                    </li>
                @empty
                    <li class="crm-muted">No notes yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if($canMailbox)
        <div class="crm-card crm-lead-mail-panel" style="margin-top:14px;">
            <div class="crm-topbar" style="margin-bottom:12px;">
                <div>
                    <h3 style="margin:0;">Emails</h3>
                    <p class="crm-muted" style="margin:4px 0 0;">Formatted conversations linked to this lead.</p>
                </div>
                <div class="crm-actions">
                    @if($canSendMail && $lead->primaryContact?->email)
                        <a class="crm-btn" href="{{ route('crm.mailbox.index', ['compose' => 1, 'to' => $lead->primaryContact->email, 'subject' => 'Re: '.$lead->title, 'lead_id' => $lead->id]) }}">Compose</a>
                    @endif
                    <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.index') }}">Open mailbox</a>
                </div>
            </div>

            @forelse($mailThreads as $thread)
                @php $latest = $thread->latestMessage; @endphp
                <article class="crm-lead-mail-thread {{ ($thread->unread_count ?? 0) > 0 ? 'is-unread' : '' }}">
                    <div class="crm-lead-mail-head">
                        <div>
                            <a class="crm-lead-mail-subject" href="{{ route('crm.mailbox.thread', $thread) }}">
                                {{ $thread->subject ?: '(no subject)' }}
                            </a>
                            @if(($thread->unread_count ?? 0) > 0)
                                <span class="crm-badge crm-badge-today">{{ $thread->unread_count }} new</span>
                            @endif
                            <div class="crm-muted" style="font-size:12px;margin-top:4px;">
                                {{ $latest?->from_name ?: $latest?->from_email ?: $thread->primary_email }}
                                · {{ $thread->message_count }} message{{ $thread->message_count === 1 ? '' : 's' }}
                                · {{ optional($thread->last_message_at)?->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                            </div>
                        </div>
                        <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.thread', $thread) }}">Open & reply</a>
                    </div>
                    @if($latest)
                        <div class="crm-lead-mail-preview">
                            @include('crm::partials.mail-body', ['message' => $latest])
                        </div>
                    @endif
                </article>
            @empty
                <div class="crm-mail-empty" style="padding:20px;">
                    <p class="crm-muted" style="margin:0;">No emails linked yet. Use Compose to email this contact.</p>
                </div>
            @endforelse
        </div>
    @endif

    <div class="crm-card" style="margin-top:14px;">
        <div class="crm-section-head">
            <div>
                <h3 style="margin:0;">Edit history</h3>
                <p class="crm-muted" style="margin:4px 0 0;">Who changed this lead and what was edited.</p>
            </div>
            @if($editActivities->count() > 0)
                <span class="crm-muted" style="font-size:12px;">{{ $editVisible->count() }} of {{ $editActivities->count() }}</span>
            @endif
        </div>
        @forelse($editVisible as $activity)
            <div class="crm-edit-block">
                <div class="crm-edit-head">
                    <strong>{{ $activity->user?->name ?? 'Unknown user' }}</strong>
                    <span class="crm-muted">edited · {{ $activity->created_at?->format('d M Y, h:i A') }}</span>
                </div>
                @if(!empty($activity->meta['changes']) && is_array($activity->meta['changes']))
                    <div class="crm-edit-table-wrap">
                        <table class="crm-table crm-edit-table">
                            <thead>
                            <tr><th>Field</th><th>From</th><th>To</th></tr>
                            </thead>
                            <tbody>
                            @foreach($activity->meta['changes'] as $change)
                                <tr>
                                    <td>{{ $change['label'] ?? $change['field'] ?? 'Field' }}</td>
                                    <td class="crm-muted">{{ $change['old'] ?? '—' }}</td>
                                    <td><strong>{{ $change['new'] ?? '—' }}</strong></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="crm-muted">{{ $activity->description ?: 'Lead updated' }}</div>
                @endif
            </div>
        @empty
            <p class="crm-muted">No edits logged yet.</p>
        @endforelse
        @if($editActivities->count() > 2)
            <div class="crm-see-more-row">
                <button class="crm-btn crm-btn-secondary" type="button" wire:click="toggleEditHistory">
                    {{ $showAllEditHistory ? 'Show less' : 'See more ('.$editHiddenCount.' more)' }}
                </button>
            </div>
        @endif
    </div>

    <div class="crm-card" style="margin-top:14px;">
        <div class="crm-section-head">
            <div>
                <h3 style="margin:0;">Timeline</h3>
                <p class="crm-muted" style="margin:4px 0 0;">Recent activity and status changes.</p>
            </div>
            @if($timelineItems->count() > 0)
                <span class="crm-muted" style="font-size:12px;">{{ $timelineVisible->count() }} of {{ $timelineItems->count() }}</span>
            @endif
        </div>
        <ul class="crm-timeline">
            @forelse($timelineVisible as $entry)
                @if($entry['kind'] === 'activity')
                    @php $activity = $entry['item']; @endphp
                    <li>
                        <strong>{{ $activity->title }}</strong>
                        <span class="crm-muted">· {{ $activity->user?->name }} · {{ $activity->created_at?->format('d M Y, h:i') }}</span>
                        @if($activity->description)<div class="crm-muted">{{ $activity->description }}</div>@endif
                        @if($activity->type === 'updated' && !empty($activity->meta['changes']))
                            <div class="crm-muted" style="margin-top:4px;">
                                Changed:
                                {{ collect($activity->meta['changes'])->pluck('label')->filter()->take(6)->join(', ') }}
                                @if(count($activity->meta['changes']) > 6)
                                    +{{ count($activity->meta['changes']) - 6 }} more
                                @endif
                            </div>
                        @endif
                    </li>
                @else
                    @php $history = $entry['item']; @endphp
                    <li>
                        <strong>Status → {{ $history->toStatus?->name }}</strong>
                        <span class="crm-muted">· {{ $history->actor?->name }} · {{ $history->created_at?->format('d M Y, h:i') }}</span>
                    </li>
                @endif
            @empty
                <li class="crm-muted">No activity yet.</li>
            @endforelse
        </ul>
        @if($timelineItems->count() > 2)
            <div class="crm-see-more-row">
                <button class="crm-btn crm-btn-secondary" type="button" wire:click="toggleTimeline">
                    {{ $showAllTimeline ? 'Show less' : 'See more ('.$timelineHiddenCount.' more)' }}
                </button>
            </div>
        @endif
    </div>
</div>
