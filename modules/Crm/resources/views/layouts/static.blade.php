@php
    $authUser = auth('crm')->user();
    $route = request()->route()?->getName();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>CodoVision CRM</title>
    <link rel="stylesheet" href="{{ asset('crm-assets/crm.css') }}">
    <script src="{{ asset('crm-assets/crm-datepicker.js') }}" defer></script>
</head>
<body class="crm-body">
<div class="crm-shell">
    <aside class="crm-sidebar">
        <div class="crm-brand">
            CodoVision CRM
            <span>{{ $authUser?->name }}</span>
        </div>
        <nav class="crm-nav">
            <a href="{{ route('crm.dashboard') }}">Dashboard</a>
            <a href="{{ route('crm.leads.index') }}">Leads</a>
            <a href="{{ route('crm.leads.kanban') }}">Pipeline</a>
            <a href="{{ route('crm.leads.create') }}">New Lead</a>
            <a href="{{ route('crm.leads.import') }}" class="active">Import CSV</a>
        </nav>
        <form method="POST" action="{{ route('crm.logout') }}" style="margin-top:24px;padding:0 8px;">
            @csrf
            <button class="crm-btn crm-btn-secondary" type="submit" style="width:100%;">Logout</button>
        </form>
    </aside>
    <main class="crm-main">
        @yield('content')
    </main>
</div>
</body>
</html>
