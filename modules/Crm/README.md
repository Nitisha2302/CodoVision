# CodoVision CRM Module (Isolated)

Removable Lead Management portal mounted at `/crm`.

## Remove this module

1. Delete the folder `modules/Crm`
2. Remove `Codovision\Crm\CrmServiceProvider::class` from `bootstrap/providers.php`
3. Remove `"Codovision\\Crm\\": "modules/Crm/src/"` from `composer.json` autoload
4. Run `composer dump-autoload`
5. Drop tables: `crm_*`
6. Delete `public/crm-assets` (optional)

Your public website code is unaffected.

## Login (seeded)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@codovision.tech | Admin@12345 |
| Manager | manager@codovision.tech | Manager@12345 |
| Executive | executive@codovision.tech | Executive@12345 |

## Seed

```bash
php artisan crm:seed
```

## Mailbox (Phase 3)

Shared CRM inbox with IMAP sync, SMTP send (queued), thread UI, lead linking, and dashboard alerts for Admin + lead assignee.

### `.env` (server-side only — never shown in UI)

```env
CRM_MAIL_HOST=smtpout.secureserver.net
CRM_MAIL_PORT=587
CRM_MAIL_USERNAME=info@codovision.tech
CRM_MAIL_PASSWORD=
CRM_MAIL_ENCRYPTION=tls
CRM_MAIL_FROM_ADDRESS=info@codovision.tech
CRM_MAIL_FROM_NAME="CodoVision CRM"

CRM_IMAP_HOST=imap.secureserver.net
CRM_IMAP_PORT=993
CRM_IMAP_USERNAME=info@codovision.tech
CRM_IMAP_PASSWORD=
CRM_IMAP_ENCRYPTION=ssl
CRM_IMAP_VALIDATE_CERT=true
```

`info@codovision.tech` authenticates on **GoDaddy SecureServer**, not Titan. SMTP falls back to `MAIL_*` if `CRM_MAIL_*` is omitted. IMAP host must be set explicitly (`CRM_IMAP_HOST`).

### Commands / workers

```bash
php artisan migrate
php artisan crm:seed
php artisan crm:mail-sync --sync          # pull inbox now
php artisan queue:work --queue=default    # send outbound mail + queued sync
php artisan schedule:work                 # or cron: * * * * * php artisan schedule:run
```

Scheduled sync runs every 5 minutes (`crm:mail-sync --sync`).

### Features

- Inbox / Sent / Starred / Archived folders
- Compose + reply (queued SMTP)
- Attachment download (auth + visibility checked)
- Link threads to leads; auto-match by contact email on inbound
- Lead page: Email contact + linked conversations
- Dashboard “New mail to reply” panel + sidebar badge for Admin and assignee

## Phases

- Phase 1–2: Auth, teams, leads, kanban, notes, import/export, follow-ups, change alerts
- Phase 3: Mailbox IMAP/SMTP sync, queue jobs, UI, new-mail alerts (implemented)
