<?php header('Content-type:text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>@yield('title') {{ setting('title') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
    @yield('content')
</body>
</html>
