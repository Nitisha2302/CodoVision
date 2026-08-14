<?php

namespace Codovision\Crm\Jobs;

use Codovision\Crm\Models\MailMessage;
use Codovision\Crm\Services\MailSendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $mailMessageId)
    {
    }

    public function handle(MailSendService $send): void
    {
        $message = MailMessage::find($this->mailMessageId);
        if (!$message || $message->direction !== 'outbound') {
            return;
        }

        if ($message->send_status === 'sent') {
            return;
        }

        $send->sendMessage($message);
    }
}
