<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Galeri</title>
    @include('partials.favicon')
    @include('partials.fonts')
    {{-- realtime.js dibutuhkan: KelolaGaleri punya listener echo GaleriUpdated. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/realtime.js'])
    @livewireStyles
</head>
<body>
    @livewire('App\Livewire\Owner\KelolaGaleri')
    @livewireScripts
</body>
</html>
