<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Superadmin - Kebunku</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    {{ $slot }}
    @livewireScripts
</body>
</html>