@php
    $authUser = auth('crm')->user();
    $route = request()->route()?->getName();
    $navMailUnread = $authUser && $authUser->can('crm.mailbox.view')
        ? \Codovision\Crm\Models\MailAlert::query()->where('user_id', $authUser->id)->infoMailbox()->unread()->count()
        : 0;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? 'CRM' }} — CodoVision CRM</title>
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('crm-assets/crm.css') }}?v=dash8">
    <script src="{{ asset('crm-assets/crm-datepicker.js') }}" defer></script>
    <script src="{{ asset('crm-assets/crm-mail-editor.js') }}" defer></script>
    <script src="{{ asset('crm-assets/crm-mail-alerts.js') }}" defer></script>
</head>
<body class="crm-body">
<div class="crm-shell" id="crmShell">
    <div class="crm-sidebar-backdrop" id="crmSidebarBackdrop" hidden></div>
    <aside class="crm-sidebar" id="crmSidebar">
        <div class="crm-brand">
            <div class="crm-brand-row">
                <div>
                    CodoVision CRM
                    <span>{{ $authUser?->name }} · {{ $authUser?->roles->first()?->name }}</span>
                </div>
                <button type="button" class="crm-sidebar-close" id="crmSidebarClose" aria-label="Close menu">×</button>
            </div>
        </div>
        <nav class="crm-nav">
            <a href="{{ route('crm.dashboard') }}" class="{{ $route === 'crm.dashboard' ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('crm.leads.index') }}" class="{{ str_starts_with((string)$route, 'crm.leads') && !str_contains((string)$route, 'kanban') && !str_contains((string)$route, 'import') ? 'active' : '' }}">Leads</a>
            <a href="{{ route('crm.leads.kanban') }}" class="{{ $route === 'crm.leads.kanban' ? 'active' : '' }}">Pipeline</a>
            <a href="{{ route('crm.leads.create') }}">New Lead</a>
            @if($authUser?->can('crm.mailbox.view'))
                <a href="{{ route('crm.mailbox.index') }}" class="{{ str_starts_with((string)$route, 'crm.mailbox') ? 'active' : '' }}">
                    Mailbox
                    <span class="crm-badge crm-badge-today" data-crm-mail-nav-badge @if($navMailUnread < 1) hidden @endif>{{ $navMailUnread }}</span>
                </a>
                <div class="crm-nav-bell">
                    <livewire:crm.mail-alert-bell />
                </div>
            @endif
            @if($authUser?->can('crm.leads.import'))
                <a href="{{ route('crm.leads.import') }}" class="{{ $route === 'crm.leads.import' ? 'active' : '' }}">Import CSV</a>
            @endif
            @if($authUser?->can('crm.users.manage'))
                <a href="{{ route('crm.users.index') }}" class="{{ $route === 'crm.users.index' ? 'active' : '' }}">Users</a>
                <a href="{{ route('crm.teams.index') }}" class="{{ $route === 'crm.teams.index' ? 'active' : '' }}">Teams</a>
            @endif
        </nav>
        <form method="POST" action="{{ route('crm.logout') }}" class="crm-logout-form">
            @csrf
            <button class="crm-btn crm-btn-secondary" type="submit" style="width:100%;">Logout</button>
        </form>
    </aside>
    <main class="crm-main">
        <div class="crm-mobile-bar">
            <button type="button" class="crm-menu-btn" id="crmMenuBtn" aria-label="Open menu">☰</button>
            <div class="crm-mobile-bar-title">CodoVision CRM</div>
            @if($authUser?->can('crm.mailbox.view'))
                <a class="crm-mobile-mail" href="{{ route('crm.mailbox.index') }}" aria-label="Mailbox">
                    ✉
                    <span class="crm-badge crm-badge-today" data-crm-mail-nav-badge @if($navMailUnread < 1) hidden @endif>{{ $navMailUnread }}</span>
                </a>
            @endif
        </div>
        @if(session('crm_success'))
            <div class="crm-alert crm-alert-success">{{ session('crm_success') }}</div>
        @endif
        @if(session('crm_error'))
            <div class="crm-alert crm-alert-error">{{ session('crm_error') }}</div>
        @endif
        {{ $slot }}
    </main>
</div>
@livewireScripts
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('crm-mail-editor-clear', (payload) => {
            const id = payload?.id || payload?.[0]?.id;
            if (id && window.CrmMailEditor) window.CrmMailEditor.clear('#' + id);
        });
    });

    (function () {
        const shell = document.getElementById('crmShell');
        const btn = document.getElementById('crmMenuBtn');
        const closeBtn = document.getElementById('crmSidebarClose');
        const backdrop = document.getElementById('crmSidebarBackdrop');
        const close = () => {
            shell?.classList.remove('crm-nav-open');
            if (backdrop) backdrop.hidden = true;
        };
        const open = () => {
            shell?.classList.add('crm-nav-open');
            if (backdrop) backdrop.hidden = false;
        };
        btn?.addEventListener('click', open);
        closeBtn?.addEventListener('click', close);
        backdrop?.addEventListener('click', close);
        document.querySelectorAll('.crm-nav a').forEach((a) => a.addEventListener('click', close));
    })();
</script>
</body>
</html>
