<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#f6f7f9]">
    @livewire('App\Livewire\Superadmin\Dashboard')
    @livewireScripts
</body>
</html>