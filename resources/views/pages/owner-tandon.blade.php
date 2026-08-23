<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitor Tandon</title>
    @include('partials.favicon')
    @include('partials.fonts')
    {{-- grafik.js (Chart.js) & realtime.js (Echo) sengaja hanya di halaman ini. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/realtime.js', 'resources/js/grafik.js'])
    @livewireStyles
</head>
<body>
    @livewire('App\Livewire\Owner\MonitorTandon')
    @livewireScripts
</body>
</html>
