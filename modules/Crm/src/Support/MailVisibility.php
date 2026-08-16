<?php

namespace Codovision\Crm\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared info@ mailbox visibility.
 * GoDaddy often files client replies into folders named like "client@email.com" —
 * those MUST stay visible. Only hide configured personal addresses.
 */
class MailVisibility
{
    /**
     * @return list<string>
     */
    public static function hiddenAddresses(): array
    {
        return collect(config('crm.mail.hidden_addresses', []))
            ->filter(fn ($email) => filled($email))
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->unique()
            ->values()
            ->all();
    }

    public static function syncContactFolders(): bool
    {
        return (bool) config('crm.mail.sync_contact_folders', true);
    }

    public static function isHiddenAddress(?string $email): bool
    {
        $email = strtolower(trim((string) $email));

        return $email !== '' && in_array($email, self::hiddenAddresses(), true);
    }

    public static function isContactFolder(?string $folder): bool
    {
        return str_contains(strtolower(trim((string) $folder)), '@');
    }

    /**
     * Skip personal/hidden contact folders (e.g. raghavtomar661@gmail.com),
     * but keep real client reply folders.
     */
    public static function shouldSkipFolder(?string $folder): bool
    {
        if (!self::isContactFolder($folder)) {
            return false;
        }

        // Folder name is often the contact email itself.
        $email = strtolower(trim((string) $folder));
        $email = basename(str_replace('\\', '/', $email));

        return self::isHiddenAddress($email);
    }

    /**
     * Threads visible in CRM mailbox (hide only personal/hidden addresses).
     */
    public static function scopeThreads(Builder $query): Builder
    {
        $hidden = self::hiddenAddresses();
        if ($hidden === []) {
            return $query;
        }

        $placeholders = implode(',', array_fill(0, count($hidden), '?'));

        return $query
            ->where(function (Builder $inner) use ($hidden, $placeholders) {
                $inner->whereNull('primary_email')
                    ->orWhereRaw("LOWER(primary_email) NOT IN ({$placeholders})", $hidden);
            })
            ->whereDoesntHave('messages', function (Builder $m) use ($hidden, $placeholders) {
                $m->where('direction', 'inbound')
                    ->whereRaw("LOWER(from_email) IN ({$placeholders})", $hidden);
            });
    }

    /**
     * Mail alerts for shared mailbox (exclude hidden personal senders).
     */
    public static function scopeAlerts(Builder $query): Builder
    {
        $hidden = self::hiddenAddresses();
        if ($hidden === []) {
            return $query;
        }

        $placeholders = implode(',', array_fill(0, count($hidden), '?'));

        return $query
            ->whereHas('message', function (Builder $m) use ($hidden, $placeholders) {
                $m->where(function (Builder $from) use ($hidden, $placeholders) {
                    $from->whereNull('from_email')
                        ->orWhere('direction', 'outbound')
                        ->orWhereRaw("LOWER(from_email) NOT IN ({$placeholders})", $hidden);
                });
            })
            ->whereDoesntHave('message.thread', function (Builder $t) use ($hidden, $placeholders) {
                $t->whereRaw("LOWER(primary_email) IN ({$placeholders})", $hidden);
            });
    }
}
