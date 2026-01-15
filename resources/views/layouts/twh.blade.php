<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Sona Template">
    <meta name="keywords" content="Sona, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', SystemHelper::appName())</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset(SystemHelper::get('favicon')) }}" type="image/x-icon">


    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700&display=swap" rel="stylesheet">

    <!-- CSS Styles -->
    <link rel="stylesheet" href="{{ asset('twh/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/elegant-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/slicknav.min.css') }}">
    <link rel="stylesheet" href="{{ asset('twh/css/style.css') }}">


    
    @stack('styles')
</head>

<body>
    @yield('content')

    <!-- Js Plugins -->
    <script src="{{ asset('twh/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('twh/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('twh/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('twh/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('twh/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('twh/js/jquery.slicknav.js') }}"></script>
    <script src="{{ asset('twh/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('twh/js/main.js') }}"></script>
    
    @stack('scripts')
</body>
</html>