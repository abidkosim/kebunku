<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login Superadmin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    @livewire('App\Livewire\Superadmin\Auth\Login')
    @livewireScripts
</body>
</html>