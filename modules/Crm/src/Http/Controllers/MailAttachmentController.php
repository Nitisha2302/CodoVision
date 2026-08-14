<?php

namespace Codovision\Crm\Http\Controllers;

use Codovision\Crm\Models\MailAttachment;
use Codovision\Crm\Models\MailThread;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MailAttachmentController extends Controller
{
    public function download(MailAttachment $attachment): StreamedResponse
    {
        $user = Auth::guard('crm')->user();
        abort_unless($user && $user->can('crm.mailbox.view'), 403);

        $attachment->loadMissing('message');
        $thread = MailThread::query()->visibleTo($user)->find($attachment->message?->thread_id);
        abort_unless($thread, 404);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->filename);
    }
}
