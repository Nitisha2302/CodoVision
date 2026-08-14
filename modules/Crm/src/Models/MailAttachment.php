<?php

namespace Codovision\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MailAttachment extends Model
{
    protected $table = 'crm_mail_attachments';

    protected $fillable = [
        'message_id',
        'filename',
        'mime',
        'size',
        'disk',
        'path',
        'content_id',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MailMessage::class, 'message_id');
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }
}
