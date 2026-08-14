<div
    wire:poll.10s="poll"
    class="crm-mail-bell"
    data-unread="{{ $unread }}"
    title="{{ $unread > 0 ? $unread.' unread mail alert(s)' : 'Mailbox alerts' }}"
>
    <a href="{{ route('crm.mailbox.index') }}" class="crm-mail-bell-link {{ $unread > 0 ? 'has-unread' : '' }}">
        <span class="crm-mail-bell-icon">✉</span>
        <span>Mail</span>
        @if($unread > 0)
            <span class="crm-badge crm-badge-today crm-mail-bell-count">{{ $unread }}</span>
        @endif
    </a>
    @if($unread > 0 && $latestSummary)
        <div class="crm-mail-bell-toast">{{ \Illuminate\Support\Str::limit($latestSummary, 70) }}</div>
    @endif
</div>
