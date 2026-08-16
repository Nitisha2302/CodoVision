<div wire:poll.10s="pollMailSync" class="crm-dashboard">
    @include('crm::partials.flash')

    <section class="crm-dash-hero">
        <div class="crm-dash-hero-copy">
            <p class="crm-dash-eyebrow">CodoVision CRM</p>
            <h2>Welcome back, {{ $user->name }}</h2>
            <p class="crm-muted">
                {{ $dateFilterLabel }} · Mail & follow-ups auto-refresh
                @if(!empty($mailSyncNote))
                    <span class="crm-badge crm-badge-today" style="margin-left:6px;">{{ $mailSyncNote }}</span>
                @endif
            </p>
        </div>
        <div class="crm-actions crm-dash-hero-actions">
            <a class="crm-btn crm-btn-secondary" href="{{ route('crm.leads.kanban') }}">Pipeline</a>
            <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.index') }}">Mailbox</a>
            <a class="crm-btn" href="{{ route('crm.leads.create') }}">+ New Lead</a>
        </div>
    </section>

    @include('crm::partials.date-filter', ['dateFilterLabel' => $dateFilterLabel])

    <div class="crm-dash-stats">
        <a class="crm-stat-card" href="{{ route('crm.leads.index') }}">
            <div class="crm-stat-card-top">
                <span class="crm-stat-icon crm-stat-icon-leads">◈</span>
                <span class="crm-muted">Leads</span>
            </div>
            <strong>{{ $totalLeads }}</strong>
            <p>Visible in selected dates</p>
        </a>
        <div class="crm-stat-card">
            <div class="crm-stat-card-top">
                <span class="crm-stat-icon crm-stat-icon-mine">◎</span>
                <span class="crm-muted">Mine</span>
            </div>
            <strong>{{ $myLeads }}</strong>
            <p>Assigned to me</p>
        </div>
        <a class="crm-stat-card {{ $dueFollowUpsCount > 0 ? 'is-alert' : '' }}" href="{{ route('crm.leads.index') }}">
            <div class="crm-stat-card-top">
                <span class="crm-stat-icon crm-stat-icon-follow">⏱</span>
                <span class="crm-muted">Follow-ups</span>
            </div>
            <strong>{{ $dueFollowUpsCount }}</strong>
            <p>Due / overdue now</p>
        </a>
        <a class="crm-stat-card {{ $mailUnreadCount > 0 ? 'is-alert' : '' }}" href="{{ route('crm.mailbox.index') }}">
            <div class="crm-stat-card-top">
                <span class="crm-stat-icon crm-stat-icon-mail">✉</span>
                <span class="crm-muted">Mail</span>
            </div>
            <strong>{{ $mailUnreadCount }}</strong>
            <p>Unread info@ alerts</p>
        </a>
        <div class="crm-stat-card crm-stat-card-value">
            <div class="crm-stat-card-top">
                <span class="crm-stat-icon crm-stat-icon-value">$</span>
                <span class="crm-muted">Pipeline</span>
            </div>
            <strong>{{ number_format((float) $pipelineValue, 0) }}</strong>
            <p>Expected value</p>
        </div>
    </div>

    @if($mailUnreadCount > 0)
        <section class="crm-card crm-dash-panel crm-dash-panel-mail">
            <div class="crm-dash-panel-head">
                <div>
                    <h3>New mail to reply</h3>
                    <p class="crm-muted">Showing {{ $mailAlerts->count() }} of {{ $mailUnreadCount }} unread · info@ inbox</p>
                </div>
                <div class="crm-actions">
                    <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.index') }}">Open mailbox</a>
                    <button class="crm-btn crm-btn-secondary" type="button" wire:click="markAllMailAlertsRead">Mark all read</button>
                </div>
            </div>
            <div class="crm-mail-alert-list">
                @foreach($mailAlerts as $alert)
                    <div class="crm-mail-alert-item crm-row-unread">
                        <div class="crm-mail-alert-main">
                            <strong>{{ $alert->summary }}</strong>
                            <div class="crm-muted">{{ \Illuminate\Support\Str::limit($alert->message?->preview() ?? '', 110) }}</div>
                            <div class="crm-mail-alert-meta">
                                @if($alert->lead)
                                    <a href="{{ route('crm.leads.show', $alert->lead) }}">{{ $alert->lead->lead_code }}</a>
                                @else
                                    <span class="crm-muted">Unlinked</span>
                                @endif
                                <span class="crm-muted">· {{ $alert->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="crm-actions">
                            <button class="crm-btn" type="button" wire:click="openMailAlert({{ $alert->id }})">Open & reply</button>
                            <button class="crm-btn crm-btn-secondary" type="button" wire:click="markMailAlertRead({{ $alert->id }})">Mark read</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="crm-mail-see-more">
                @if(!$showAllMailAlerts && $mailAlertsHiddenCount > 0)
                    <button class="crm-btn crm-btn-secondary" type="button" wire:click="toggleMailAlerts">See more ({{ $mailAlertsHiddenCount }} more)</button>
                    <a class="crm-btn" href="{{ route('crm.mailbox.index') }}">View all mails</a>
                @elseif($showAllMailAlerts && $mailUnreadCount > 3)
                    <button class="crm-btn crm-btn-secondary" type="button" wire:click="toggleMailAlerts">Show less</button>
                    <a class="crm-btn" href="{{ route('crm.mailbox.index') }}">Open full mailbox</a>
                @else
                    <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.index') }}">View all mails</a>
                @endif
            </div>
        </section>
    @endif

    @if($isAdmin && $unreadCount > 0)
        <section class="crm-card crm-dash-panel crm-dash-panel-changes">
            <div class="crm-dash-panel-head">
                <div>
                    <h3>Team changes</h3>
                    <p class="crm-muted">{{ $unreadCount }} unread lead update{{ $unreadCount === 1 ? '' : 's' }} from your team</p>
                </div>
                <button class="crm-btn crm-btn-secondary" type="button" wire:click="markAllAlertsRead">Mark all as read</button>
            </div>

            <div class="crm-dash-change-list">
                @foreach($unreadAlerts as $alert)
                    <article class="crm-dash-change-item">
                        <div class="crm-dash-change-main">
                            <div class="crm-dash-change-title">
                                <strong>{{ $alert->lead?->lead_code ?? '—' }}</strong>
                                <span class="crm-muted">{{ $alert->lead?->primaryContact?->fullName() }}</span>
                            </div>
                            <p>{{ $alert->summary }}</p>
                            @if(!empty($alert->meta['description']))
                                <p class="crm-muted">{{ $alert->meta['description'] }}</p>
                            @endif
                            @if(!empty($alert->meta['changes']) && is_array($alert->meta['changes']))
                                <div class="crm-muted crm-dash-change-diff">
                                    @foreach(array_slice($alert->meta['changes'], 0, 3) as $change)
                                        <div>{{ $change['label'] ?? 'Field' }}: {{ $change['old'] ?? '—' }} → {{ $change['new'] ?? '—' }}</div>
                                    @endforeach
                                    @if(count($alert->meta['changes']) > 3)
                                        <div>+{{ count($alert->meta['changes']) - 3 }} more fields</div>
                                    @endif
                                </div>
                            @endif
                            <div class="crm-dash-change-meta">
                                <span>{{ $alert->actor?->name ?? '—' }}</span>
                                <span class="crm-muted">· {{ $alert->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="crm-actions">
                            <button class="crm-btn" type="button" wire:click="openLeadAndMarkRead({{ $alert->lead_id }})">Open</button>
                            <button class="crm-btn crm-btn-secondary" type="button" wire:click="markAlertRead({{ $alert->id }})">Mark read</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <div class="crm-dash-split">
        <section class="crm-card crm-dash-panel">
            <div class="crm-dash-panel-head">
                <div>
                    <h3>Follow-ups due</h3>
                    <p class="crm-muted">Overdue or due now</p>
                </div>
            </div>
            <div class="crm-dash-list">
                @forelse($followUpsDue as $lead)
                    <a class="crm-dash-list-item is-{{ $lead->followUpState() }}" href="{{ route('crm.leads.show', $lead) }}">
                        <div>
                            <strong>{{ $lead->lead_code }}</strong>
                            <div class="crm-muted">{{ $lead->primaryContact?->fullName() ?: '—' }}</div>
                            <div class="crm-muted crm-dash-note">{{ $lead->follow_up_note ?: 'No note' }}</div>
                        </div>
                        <span class="crm-badge crm-badge-{{ $lead->followUpState() }}">{{ $lead->next_follow_up_at?->format('d M, h:i A') }}</span>
                    </a>
                @empty
                    <div class="crm-dash-empty">No overdue follow-ups. Nice work.</div>
                @endforelse
            </div>
        </section>

        <section class="crm-card crm-dash-panel">
            <div class="crm-dash-panel-head">
                <div>
                    <h3>Upcoming (48h)</h3>
                    <p class="crm-muted">Next follow-ups scheduled</p>
                </div>
            </div>
            <div class="crm-dash-list">
                @forelse($followUpsUpcoming as $lead)
                    <a class="crm-dash-list-item is-{{ $lead->followUpState() }}" href="{{ route('crm.leads.show', $lead) }}">
                        <div>
                            <strong>{{ $lead->lead_code }}</strong>
                            <div class="crm-muted">{{ $lead->primaryContact?->fullName() ?: '—' }}</div>
                            <div class="crm-muted crm-dash-note">{{ $lead->assignee?->name ?? 'Unassigned' }}</div>
                        </div>
                        <span class="crm-badge crm-badge-{{ $lead->followUpState() }}">{{ $lead->next_follow_up_at?->format('d M, h:i A') }}</span>
                    </a>
                @empty
                    <div class="crm-dash-empty">No upcoming follow-ups in the next 48 hours.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="crm-dash-split">
        <section class="crm-card crm-dash-panel">
            <div class="crm-dash-panel-head">
                <div>
                    <h3>Funnel</h3>
                    <p class="crm-muted">Leads by stage for selected dates</p>
                </div>
                <a class="crm-btn crm-btn-secondary crm-btn-sm" href="{{ route('crm.leads.kanban') }}">Open pipeline</a>
            </div>
            @php $funnelMax = max(1, (int) $statusCounts->max('leads_count')); @endphp
            <div class="crm-funnel">
                @foreach($statusCounts as $status)
                    @php $pct = round(((int) $status->leads_count / $funnelMax) * 100); @endphp
                    <div class="crm-funnel-row">
                        <div class="crm-funnel-label">
                            <span class="crm-funnel-dot" style="background:{{ $status->color ?: '#6366f1' }}"></span>
                            <span>{{ $status->name }}</span>
                        </div>
                        <div class="crm-funnel-track">
                            <div class="crm-funnel-fill" style="width:{{ $pct }}%;background:{{ $status->color ?: '#6366f1' }}"></div>
                        </div>
                        <strong>{{ $status->leads_count }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="crm-card crm-dash-panel">
            <div class="crm-dash-panel-head">
                <div>
                    <h3>Latest leads</h3>
                    <p class="crm-muted">Recently updated in this range</p>
                </div>
                <a class="crm-btn crm-btn-secondary crm-btn-sm" href="{{ route('crm.leads.index') }}">View all</a>
            </div>
            <div class="crm-dash-list">
                @forelse($recentLeads as $lead)
                    <a class="crm-dash-list-item {{ $isAdmin && ($lead->unread_alerts_count ?? 0) > 0 ? 'is-unread' : '' }} {{ $lead->followUpState() ? 'is-'.$lead->followUpState() : '' }}" href="{{ route('crm.leads.show', $lead) }}">
                        <div>
                            <strong>
                                {{ $lead->lead_code }}
                                @if($isAdmin && ($lead->unread_alerts_count ?? 0) > 0)
                                    <span class="crm-dot-new" title="Unread team changes"></span>
                                @endif
                            </strong>
                            <div class="crm-muted">{{ $lead->primaryContact?->fullName() ?? '—' }}</div>
                        </div>
                        <div class="crm-dash-list-right">
                            @if($lead->next_follow_up_at)
                                <span class="crm-badge crm-badge-{{ $lead->followUpState() }}">{{ $lead->next_follow_up_at->format('d M') }}</span>
                            @endif
                            <span class="crm-badge">{{ $lead->status?->name }}</span>
                        </div>
                    </a>
                @empty
                    <div class="crm-dash-empty">No leads yet for this date range.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
