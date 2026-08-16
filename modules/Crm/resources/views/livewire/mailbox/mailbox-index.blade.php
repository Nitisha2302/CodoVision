<div wire:poll.10s="pollMailSync" class="crm-mailbox-page">
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">Mailbox @if($unreadMail > 0)<span class="crm-badge crm-badge-today">{{ $unreadMail }} new</span>@endif</h2>
            <p class="crm-muted" style="margin:4px 0 0;">
                Shared info@ inbox ·
                @if($imapReady)
                    <span class="crm-mail-status ok">IMAP connected</span>
                @else
                    <span class="crm-mail-status bad">IMAP not configured</span>
                @endif
                <span class="crm-muted"> · client replies synced</span>
            </p>
        </div>
        <div class="crm-actions">
            @if($canSync)
                <button class="crm-btn crm-btn-secondary" type="button" wire:click="syncNow" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="syncNow">↻ Sync</span>
                    <span wire:loading wire:target="syncNow">Syncing…</span>
                </button>
            @endif
            @if($canSend)
                <button class="crm-btn" type="button" wire:click="openCompose">Compose</button>
            @endif
        </div>
    </div>

    @unless($imapReady)
        <div class="crm-alert crm-alert-error">
            Add IMAP credentials in <code>.env</code> (<code>CRM_IMAP_HOST</code>, <code>CRM_IMAP_USERNAME</code>, <code>CRM_IMAP_PASSWORD</code>).
            Passwords are never shown in the UI.
        </div>
    @endunless

    <div class="crm-mail-layout">
        <aside class="crm-mail-folders crm-card">
            <button type="button" class="{{ $folder === 'inbox' ? 'active' : '' }}" wire:click="$set('folder','inbox')">
                <span>Inbox</span>
                @if(($folderCounts['inbox'] ?? 0) > 0)<span class="crm-mail-count">{{ $folderCounts['inbox'] }}</span>@endif
            </button>
            <button type="button" class="{{ $folder === 'starred' ? 'active' : '' }}" wire:click="$set('folder','starred')">
                <span>Starred</span>
                @if(($folderCounts['starred'] ?? 0) > 0)<span class="crm-mail-count">{{ $folderCounts['starred'] }}</span>@endif
            </button>
            <button type="button" class="{{ $folder === 'sent' ? 'active' : '' }}" wire:click="$set('folder','sent')">
                <span>Sent</span>
            </button>
            <button type="button" class="{{ $folder === 'archived' ? 'active' : '' }}" wire:click="$set('folder','archived')">
                <span>Archived</span>
                @if(($folderCounts['archived'] ?? 0) > 0)<span class="crm-mail-count">{{ $folderCounts['archived'] }}</span>@endif
            </button>
        </aside>

        <section class="crm-card crm-mail-list-panel">
            <div class="crm-mail-list-toolbar">
                <input class="crm-input" wire:model.live.debounce.300ms="search" placeholder="Search mail">
            </div>

            <div class="crm-mail-list">
                @forelse($threads as $thread)
                    @php
                        $latest = $thread->latestMessage;
                        $unread = ($thread->inbound_unread ?? 0) > 0;
                        $starred = (bool) ($latest?->is_starred);
                    @endphp
                    <div class="crm-mail-row {{ $unread ? 'is-unread' : '' }}">
                        <button type="button" class="crm-mail-star {{ $starred ? 'on' : '' }}" wire:click.stop="toggleStarThread({{ $thread->id }})" title="Star">
                            {{ $starred ? '★' : '☆' }}
                        </button>
                        <a class="crm-mail-row-main" href="{{ route('crm.mailbox.thread', $thread) }}">
                            <div class="crm-mail-from">
                                {{ $latest?->from_name ?: ($latest?->from_email ?: $thread->primary_email ?: 'Unknown') }}
                            </div>
                            <div class="crm-mail-snippet">
                                <span class="crm-mail-subject">{{ $thread->subject ?: '(no subject)' }}</span>
                                <span class="crm-muted"> — {{ \Illuminate\Support\Str::limit($latest?->preview() ?? '', 90) }}</span>
                                @if($latest?->has_attachments)<span class="crm-mail-clip" title="Has attachment">📎</span>@endif
                                @if($thread->lead)
                                    <span class="crm-badge">{{ $thread->lead->lead_code }}</span>
                                @endif
                            </div>
                            <div class="crm-mail-date">{{ optional($thread->last_message_at)?->timezone('Asia/Kolkata')->format('d M, h:i A') }}</div>
                        </a>
                        <div class="crm-mail-row-actions">
                            @if($unread)
                                <button type="button" class="crm-icon-btn" title="Mark read" wire:click.stop="markThreadRead({{ $thread->id }}, true)">✓</button>
                            @else
                                <button type="button" class="crm-icon-btn" title="Mark unread" wire:click.stop="markThreadRead({{ $thread->id }}, false)">●</button>
                            @endif
                            @unless($thread->is_archived)
                                <button type="button" class="crm-icon-btn" title="Archive" wire:click.stop="archiveThread({{ $thread->id }})">⬇</button>
                            @endunless
                        </div>
                    </div>
                @empty
                    <div class="crm-mail-empty">
                        <h3>No conversations</h3>
                        <p class="crm-muted">Sync the inbox or compose a new email to get started.</p>
                        @if($canSend)
                            <button class="crm-btn" type="button" wire:click="openCompose">Compose</button>
                        @endif
                    </div>
                @endforelse
            </div>

            @if($threads->hasPages())
                <div class="crm-actions" style="margin-top:12px;align-items:center;">
                    <button type="button" class="crm-btn crm-btn-secondary" wire:click="previousPage" @disabled($threads->onFirstPage())>Previous</button>
                    <span class="crm-muted">Page {{ $threads->currentPage() }} of {{ $threads->lastPage() }}</span>
                    <button type="button" class="crm-btn crm-btn-secondary" wire:click="nextPage" @disabled(!$threads->hasMorePages())>Next</button>
                </div>
            @endif
        </section>
    </div>

    @if($showCompose)
        <div class="crm-modal-backdrop" wire:click.self="closeCompose">
            <div class="crm-compose-window" wire:click.stop>
                <div class="crm-compose-header">
                    <strong>New Message</strong>
                    <button type="button" class="crm-icon-btn" wire:click="closeCompose" title="Close">✕</button>
                </div>
                <form wire:submit="sendCompose" class="crm-compose-form">
                    <div class="crm-compose-fields">
                        <div class="crm-compose-line">
                            <label>To</label>
                            <input wire:model="compose_to" placeholder="recipient@example.com" autocomplete="off">
                            <button type="button" class="crm-linkish" wire:click="$toggle('showCc')">Cc</button>
                        </div>
                        @include('crm::partials.field-error', ['name' => 'compose_to'])

                        @if($showCc)
                            <div class="crm-compose-line">
                                <label>Cc</label>
                                <input wire:model="compose_cc" placeholder="optional@example.com" autocomplete="off">
                            </div>
                        @endif

                        <div class="crm-compose-line">
                            <label>Subject</label>
                            <input wire:model="compose_subject" placeholder="Subject" autocomplete="off">
                        </div>
                        @include('crm::partials.field-error', ['name' => 'compose_subject'])

                        <div class="crm-compose-line">
                            <label>Lead</label>
                            <select wire:model="compose_lead_id">
                                <option value="">No linked lead</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}">{{ $lead->lead_code }} — {{ $lead->primaryContact?->fullName() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @include('crm::partials.mail-editor', [
                        'model' => 'compose_body',
                        'editorId' => 'rte-compose-body',
                        'placeholder' => 'Write your message…',
                        'showToolbar' => $showToolbar,
                    ])
                    @include('crm::partials.field-error', ['name' => 'compose_body'])

                    @if(count($compose_files))
                        <div class="crm-mail-attach-list" style="padding:8px 12px;">
                            @foreach($compose_files as $i => $file)
                                <span class="crm-mail-attach-chip">
                                    📎 {{ method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'file' }}
                                    <button type="button" class="crm-icon-btn" wire:click="removeComposeFile({{ $i }})">✕</button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="crm-compose-footer">
                        <div class="crm-compose-send-group">
                            <button class="crm-btn crm-send-btn" type="submit" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="sendCompose">Send</span>
                                <span wire:loading wire:target="sendCompose">Sending…</span>
                            </button>
                        </div>
                        <button type="button" class="crm-icon-btn {{ $showToolbar ? 'on' : '' }}" title="Formatting" wire:click="$toggle('showToolbar')">Aa</button>
                        <label class="crm-icon-btn" title="Attach files">
                            📎
                            <input type="file" wire:model="compose_files" multiple hidden>
                        </label>
                        <button type="button" class="crm-icon-btn" title="Insert link" onclick="(function(){var e=document.getElementById('rte-compose-body');if(!e)return;e.focus();var u=prompt('Enter link URL','https://');if(u)document.execCommand('createLink',false,u);})()">🔗</button>
                        <button type="button" class="crm-icon-btn" title="Insert image" onclick="(function(){var e=document.getElementById('rte-compose-body');if(!e)return;e.focus();var u=prompt('Enter image URL','https://');if(u)document.execCommand('insertImage',false,u);})()">🖼</button>
                        <span class="crm-compose-spacer"></span>
                        <button type="button" class="crm-icon-btn" title="Discard" wire:click="discardCompose">🗑</button>
                    </div>
                    <div wire:loading wire:target="compose_files" class="crm-muted" style="padding:0 12px 10px;">Uploading attachment…</div>
                </form>
            </div>
        </div>
    @endif
</div>
