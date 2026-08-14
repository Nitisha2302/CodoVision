<?php

namespace Codovision\Crm\Support;

class MailboxAddress
{
    /**
     * @return list<string>
     */
    public static function ownAddresses(): array
    {
        return collect([
            config('crm.mail.smtp.from_address'),
            config('crm.mail.smtp.username'),
            config('crm.mail.imap.username'),
            config('mail.from.address'),
            env('MAIL_USERNAME'),
            env('CRM_MAIL_USERNAME'),
            env('CRM_IMAP_USERNAME'),
        ])
            ->filter(fn ($e) => filled($e))
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->unique()
            ->values()
            ->all();
    }

    public static function isOwn(?string $email): bool
    {
        $email = strtolower(trim((string) $email));

        return $email !== '' && in_array($email, self::ownAddresses(), true);
    }
}
