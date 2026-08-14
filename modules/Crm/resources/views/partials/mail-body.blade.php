@php
    use Codovision\Crm\Support\MailHtml;
    $html = MailHtml::sanitize($message->body_html ?? '');
    $text = $message->body_text ?: MailHtml::toText($html);
@endphp
<div class="crm-mail-body">
    @if($html !== '')
        {!! $html !!}
    @else
        {!! nl2br(e($text)) !!}
    @endif
</div>
@if(($message->attachments ?? collect())->isNotEmpty())
    <div class="crm-mail-attach-list">
        @foreach($message->attachments as $attachment)
            <a class="crm-mail-attach-chip" href="{{ route('crm.mailbox.attachment', $attachment) }}">
                <span>📎</span>
                <span>{{ $attachment->filename }}</span>
                @if($attachment->size)
                    <span class="crm-muted">{{ number_format($attachment->size / 1024, 1) }} KB</span>
                @endif
            </a>
        @endforeach
    </div>
@endif
