<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? 'CRM Login' }} — CodoVision CRM</title>
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('crm-assets/crm.css') }}">
</head>
<body class="crm-body">
    {{ $slot }}
    @livewireScripts
</body>
</html>
