<div wire:poll.10s="pollMailSync">
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">Dashboard</h2>
            <p class="crm-muted" style="margin:4px 0 0;">Welcome back, {{ $user->name }} · Mail & follow-ups auto-check every 30s</p>
            @if(!empty($mailSyncNote))
                <p class="crm-badge crm-badge-today" style="margin-top:6px;">{{ $mailSyncNote }}</p>
            @endif
        </div>
        <a class="crm-btn" href="{{ route('crm.leads.create') }}">+ New Lead</a>
    </div>

    <div class="crm-grid crm-grid-4" style="margin-bottom:16px;">
        <div class="crm-card crm-stat"><h3>{{ $totalLeads }}</h3><p>Visible leads</p></div>
        <div class="crm-card crm-stat"><h3>{{ $myLeads }}</h3><p>Assigned to me</p></div>
        <div class="crm-card crm-stat @if($dueFollowUpsCount > 0) crm-stat-alert @endif">
            <h3>{{ $dueFollowUpsCount }}</h3>
            <p>Follow-ups due</p>
        </div>
        <div class="crm-card crm-stat @if($mailUnreadCount > 0) crm-stat-alert @endif">
            <h3>{{ $mailUnreadCount }}</h3>
            <p>New mail alerts</p>
        </div>
    </div>

    @if($mailUnreadCount > 0)
        <div class="crm-card crm-highlight-panel" style="margin-bottom:14px;">
            <div class="crm-topbar" style="margin-bottom:8px;">
                <div>
                    <h3 style="margin:0;">New mail to reply</h3>
                    <p class="crm-muted" style="margin:4px 0 0;">Highlighted for Admin and the assigned salesperson. Open to reply.</p>
                </div>
                <div class="crm-actions">
                    <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.index') }}">Open mailbox</a>
                    <button class="crm-btn crm-btn-secondary" type="button" wire:click="markAllMailAlertsRead">Mark all read</button>
                </div>
            </div>
            <table class="crm-table">
                <thead><tr><th>From / Subject</th><th>Lead</th><th>When</th><th></th></tr></thead>
                <tbody>
                @foreach($mailAlerts as $alert)
                    <tr class="crm-row-unread">
                        <td>
                            <strong>{{ $alert->summary }}</strong>
                            <div class="crm-muted">{{ \Illuminate\Support\Str::limit($alert->message?->preview() ?? '', 90) }}</div>
                        </td>
                        <td>
                            @if($alert->lead)
                                <a href="{{ route('crm.leads.show', $alert->lead) }}">{{ $alert->lead->lead_code }}</a>
                            @else
                                <span class="crm-muted">Unlinked</span>
                            @endif
                        </td>
                        <td class="crm-muted">{{ $alert->created_at?->diffForHumans() }}</td>
                        <td>
                            <div class="crm-actions">
                                <button class="crm-btn" type="button" wire:click="openMailAlert({{ $alert->id }})">Open & reply</button>
                                <button class="crm-btn crm-btn-secondary" type="button" wire:click="markMailAlertRead({{ $alert->id }})">Mark read</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($isAdmin && $unreadCount > 0)
        <div class="crm-card crm-highlight-panel" style="margin-bottom:14px;">
            <div class="crm-topbar" style="margin-bottom:8px;">
                <div>
                    <h3 style="margin:0;">Highlighted team changes</h3>
                    <p class="crm-muted" style="margin:4px 0 0;">Leads updated by team members. Open or mark as read to clear highlight.</p>
                </div>
                <button class="crm-btn crm-btn-secondary" type="button" wire:click="markAllAlertsRead">Mark all as read</button>
            </div>
            <table class="crm-table">
                <thead>
                <tr><th>Lead</th><th>Change</th><th>By</th><th>When</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($unreadAlerts as $alert)
                    <tr class="crm-row-unread">
                        <td>
                            <strong>{{ $alert->lead?->lead_code ?? '—' }}</strong>
                            <div class="crm-muted">{{ $alert->lead?->primaryContact?->fullName() }}</div>
                        </td>
                        <td>
                            {{ $alert->summary }}
                            @if(!empty($alert->meta['description']))
                                <div class="crm-muted">{{ $alert->meta['description'] }}</div>
                            @endif
                            @if(!empty($alert->meta['changes']) && is_array($alert->meta['changes']))
                                <div class="crm-muted" style="margin-top:4px;">
                                    @foreach(array_slice($alert->meta['changes'], 0, 4) as $change)
                                        <div>{{ $change['label'] ?? 'Field' }}: {{ $change['old'] ?? '—' }} → {{ $change['new'] ?? '—' }}</div>
                                    @endforeach
                                    @if(count($alert->meta['changes']) > 4)
                                        <div>+{{ count($alert->meta['changes']) - 4 }} more fields</div>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>{{ $alert->actor?->name ?? '—' }}</td>
                        <td>{{ $alert->created_at?->diffForHumans() }}</td>
                        <td>
                            <div class="crm-actions">
                                <button class="crm-btn crm-btn-secondary" type="button" wire:click="openLeadAndMarkRead({{ $alert->lead_id }})">Open</button>
                                <button class="crm-btn crm-btn-secondary" type="button" wire:click="markAlertRead({{ $alert->id }})">Mark read</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="crm-grid crm-grid-2" style="margin-bottom:14px;">
        <div class="crm-card">
            <h3 style="margin-top:0;">Follow-ups due / overdue (within 1 min)</h3>
            <table class="crm-table">
                <thead><tr><th>Lead</th><th>When</th><th>Note</th><th></th></tr></thead>
                <tbody>
                @forelse($followUpsDue as $lead)
                    <tr class="crm-row-followup-{{ $lead->followUpState() }}">
                        <td>
                            <a href="{{ route('crm.leads.show', $lead) }}">{{ $lead->lead_code }}</a>
                            <div class="crm-muted">{{ $lead->primaryContact?->fullName() }}</div>
                        </td>
                        <td>
                            <span class="crm-badge crm-badge-{{ $lead->followUpState() }}">
                                {{ $lead->next_follow_up_at?->format('d M, h:i A') }}
                            </span>
                        </td>
                        <td class="crm-muted">{{ $lead->follow_up_note ?: '—' }}</td>
                        <td><a href="{{ route('crm.leads.show', $lead) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="crm-muted">No overdue follow-ups. Nice work.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="crm-card">
            <h3 style="margin-top:0;">Upcoming follow-ups (48h)</h3>
            <table class="crm-table">
                <thead><tr><th>Lead</th><th>When</th><th>Assignee</th><th></th></tr></thead>
                <tbody>
                @forelse($followUpsUpcoming as $lead)
                    <tr class="crm-row-followup-{{ $lead->followUpState() }}">
                        <td>
                            <a href="{{ route('crm.leads.show', $lead) }}">{{ $lead->lead_code }}</a>
                            <div class="crm-muted">{{ $lead->primaryContact?->fullName() }}</div>
                        </td>
                        <td>
                            <span class="crm-badge crm-badge-{{ $lead->followUpState() }}">
                                {{ $lead->next_follow_up_at?->format('d M, h:i A') }}
                            </span>
                        </td>
                        <td>{{ $lead->assignee?->name ?? '—' }}</td>
                        <td><a href="{{ route('crm.leads.show', $lead) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="crm-muted">No upcoming follow-ups in the next 48 hours.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="crm-grid crm-grid-2">
        <div class="crm-card">
            <h3 style="margin-top:0;">Funnel</h3>
            @foreach($statusCounts as $status)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--crm-border);font-size:13px;">
                    <span><span class="crm-badge" style="background:{{ $status->color }}22;color:{{ $status->color }}">{{ $status->name }}</span></span>
                    <strong>{{ $status->leads_count }}</strong>
                </div>
            @endforeach
        </div>
        <div class="crm-card">
            <h3 style="margin-top:0;">Latest leads</h3>
            <table class="crm-table">
                <thead><tr><th>Code</th><th>Contact</th><th>Follow-up</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentLeads as $lead)
                    <tr @class([
                        'crm-row-unread' => $isAdmin && ($lead->unread_alerts_count ?? 0) > 0,
                        'crm-row-followup-'.$lead->followUpState() => (bool) $lead->followUpState(),
                    ])>
                        <td>
                            <a href="{{ route('crm.leads.show', $lead) }}">{{ $lead->lead_code }}</a>
                            @if($isAdmin && ($lead->unread_alerts_count ?? 0) > 0)
                                <span class="crm-dot-new" title="Unread team changes"></span>
                            @endif
                        </td>
                        <td>{{ $lead->primaryContact?->fullName() ?? '—' }}</td>
                        <td>
                            @if($lead->next_follow_up_at)
                                <span class="crm-badge crm-badge-{{ $lead->followUpState() }}">
                                    {{ $lead->next_follow_up_at->format('d M, h:i A') }}
                                </span>
                            @else
                                <span class="crm-muted">—</span>
                            @endif
                        </td>
                        <td><span class="crm-badge">{{ $lead->status?->name }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="crm-muted">No leads yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
