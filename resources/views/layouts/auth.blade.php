<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('page-title') - </title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ url("assets/images/favicon.png") }}"/>

    <!-- Themify icons -->
    <link rel="stylesheet" href="{{ url("dist/icons/themify-icons/themify-icons.css") }}" type="text/css">

    <!-- Main style file -->
    <link rel="stylesheet" href="{{ url("dist/css/app.min.css") }}" type="text/css">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="auth">

<!-- begin::preloader-->
<div class="preloader">
    <div class="preloader-icon"></div>
</div>
<!-- end::preloader -->

@yield('content')

<!-- Bundle scripts -->
<script src="{{ url("libs/bundle.js") }}"></script>

<!-- Main Javascript file -->
<script src="{{ url("dist/js/app.min.js") }}"></script>
</body>
</html>
