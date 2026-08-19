<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Gruppa Info')">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('app.css') }}">
</head>
<body>
    @yield('content')

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/lucide/lucide.min.js') }}"></script>
    <script src="{{ asset('app.js') }}"></script>
</body>
</html>
