@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Darajat Edu - {{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/main/auth.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/icons/DarajatTrans.png') }}" sizes="192x192" type="image/png">
</head>

<body class="gradient-bg">
    <div class="min-h-screen">
        <!-- Page Content -->
        <main>
            <!-- Platform Name -->
        <div class="brand">
            <span class="gradient-text"><img src="{{asset('img/icons/DarajatTrans.png')}}" alt="logo"></span>
        </div>
            {{ $slot}}
        </main>
    </div>
</body>
</html>