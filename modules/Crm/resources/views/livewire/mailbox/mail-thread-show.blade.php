<div class="crm-mailbox-page">
    @include('crm::partials.flash')

    <div class="crm-topbar">
        <div>
            <h2 style="margin:0;">{{ $thread->subject ?: '(no subject)' }}</h2>
            <p class="crm-muted" style="margin:4px 0 0;">
                {{ $thread->primary_email }}
                · {{ $thread->message_count }} message{{ $thread->message_count === 1 ? '' : 's' }}
                @if($thread->lead)
                    · Lead <a href="{{ route('crm.leads.show', $thread->lead) }}">{{ $thread->lead->lead_code }}</a>
                @endif
            </p>
        </div>
        <div class="crm-actions">
            <a class="crm-btn crm-btn-secondary" href="{{ route('crm.mailbox.index') }}">← Inbox</a>
            <button class="crm-btn crm-btn-secondary" type="button" wire:click="toggleArchive">
                {{ $thread->is_archived ? 'Move to Inbox' : 'Archive' }}
            </button>
        </div>
    </div>

    <div class="crm-card crm-mail-lead-bar">
        <form wire:submit="linkLead" class="crm-actions" style="align-items:end;width:100%;">
            <div class="crm-field" style="margin:0;flex:1;min-width:220px;">
                <label class="crm-label">Link to lead</label>
                <select class="crm-select" wire:model="link_lead_id">
                    <option value="">None</option>
                    @foreach($leads as $lead)
                        <option value="{{ $lead->id }}">{{ $lead->lead_code }} — {{ $lead->primaryContact?->fullName() }} ({{ $lead->primaryContact?->email }})</option>
                    @endforeach
                </select>
            </div>
            <button class="crm-btn crm-btn-secondary" type="submit">Save link</button>
            @if($thread->lead?->primaryContact?->email && $canSend)
                <a class="crm-btn" href="{{ route('crm.mailbox.index', ['compose' => 1, 'to' => $thread->lead->primaryContact->email, 'subject' => 'Re: '.($thread->subject ?: $thread->lead->title), 'lead_id' => $thread->lead->id]) }}">New email to contact</a>
            @endif
        </form>
    </div>

    <div class="crm-mail-thread">
        @foreach($thread->messages as $message)
            <article class="crm-mail-bubble {{ $message->direction === 'outbound' ? 'outbound' : 'inbound' }}">
                <header>
                    <div class="crm-mail-avatar">{{ strtoupper(substr($message->from_name ?: $message->from_email ?: '?', 0, 1)) }}</div>
                    <div class="crm-mail-meta">
                        <strong>
                            {{ $message->direction === 'outbound' ? ($message->from_name ?: 'You') : ($message->from_name ?: $message->from_email) }}
                        </strong>
                        <div class="crm-muted" style="font-size:12px;">
                            to {{ implode(', ', $message->to_emails ?: []) }}
                            @if(!empty($message->cc_emails))
                                · cc {{ implode(', ', $message->cc_emails) }}
                            @endif
                        </div>
                    </div>
                    <div class="crm-mail-meta-right">
                        <span class="crm-muted">{{ $message->sent_at?->format('d M Y, h:i A') }}</span>
                        @if($message->direction === 'outbound' && $message->send_status)
                            <span class="crm-badge">{{ ucfirst($message->send_status) }}</span>
                        @endif
                        <button class="crm-icon-btn {{ $message->is_starred ? 'on' : '' }}" type="button" wire:click="toggleStar({{ $message->id }})">
                            {{ $message->is_starred ? '★' : '☆' }}
                        </button>
                    </div>
                </header>

                @include('crm::partials.mail-body', ['message' => $message])

                @if($message->send_error)
                    <div class="crm-alert crm-alert-error" style="margin-top:8px;">{{ $message->send_error }}</div>
                @endif
            </article>
        @endforeach
    </div>

    @if($canSend)
        <div class="crm-compose-window crm-compose-inline" style="margin-top:14px;">
            <div class="crm-compose-header">
                <div>
                    <strong>Reply</strong>
                    @php $replyTo = $thread->replyRecipient(); @endphp
                    @if($replyTo)
                        <div class="crm-muted" style="font-size:12px;margin-top:2px;">To: {{ $replyTo }}</div>
                    @else
                        <div class="crm-muted" style="font-size:12px;margin-top:2px;color:#fca5a5;">No external recipient on this thread</div>
                    @endif
                </div>
                <button type="button" class="crm-linkish" wire:click="$toggle('showCc')">{{ $showCc ? 'Hide Cc' : 'Cc' }}</button>
            </div>
            <form wire:submit="reply" class="crm-compose-form">
                @if($showCc)
                    <div class="crm-compose-fields">
                        <div class="crm-compose-line">
                            <label>Cc</label>
                            <input wire:model="reply_cc" placeholder="optional@example.com">
                        </div>
                    </div>
                @endif

                @include('crm::partials.mail-editor', [
                    'model' => 'reply_body',
                    'editorId' => 'rte-reply-body',
                    'placeholder' => 'Write your reply…',
                    'showToolbar' => $showToolbar,
                ])
                @include('crm::partials.field-error', ['name' => 'reply_body'])

                @if(count($reply_files))
                    <div class="crm-mail-attach-list" style="padding:8px 12px;">
                        @foreach($reply_files as $i => $file)
                            <span class="crm-mail-attach-chip">
                                📎 {{ method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'file' }}
                                <button type="button" class="crm-icon-btn" wire:click="removeReplyFile({{ $i }})">✕</button>
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="crm-compose-footer">
                    <div class="crm-compose-send-group">
                        <button class="crm-btn crm-send-btn" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="reply">Send</span>
                            <span wire:loading wire:target="reply">Sending…</span>
                        </button>
                    </div>
                    <button type="button" class="crm-icon-btn {{ $showToolbar ? 'on' : '' }}" title="Formatting" wire:click="$toggle('showToolbar')">Aa</button>
                    <label class="crm-icon-btn" title="Attach files">
                        📎
                        <input type="file" wire:model="reply_files" multiple hidden>
                    </label>
                    <button type="button" class="crm-icon-btn" title="Insert link" onclick="document.getElementById('rte-reply-body')?.focus();document.execCommand('createLink', false, prompt('Enter link URL','https://'));">🔗</button>
                    <button type="button" class="crm-icon-btn" title="Insert image" onclick="document.getElementById('rte-reply-body')?.focus();document.execCommand('insertImage', false, prompt('Enter image URL','https://'));">🖼</button>
                    <span class="crm-compose-spacer"></span>
                    <button type="button" class="crm-icon-btn" title="Discard" wire:click="discardReply">🗑</button>
                </div>
                <div wire:loading wire:target="reply_files" class="crm-muted" style="padding:0 12px 10px;">Uploading attachment…</div>
            </form>
        </div>
    @endif
</div>
