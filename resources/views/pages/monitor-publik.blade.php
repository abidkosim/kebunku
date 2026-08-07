<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Monitor Tandon</title>
    @include('partials.fonts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-950">
    @livewire('App\Livewire\Public\MonitorPublik', ['kunci' => $kunci])
    @livewireScripts
</body>
</html>
