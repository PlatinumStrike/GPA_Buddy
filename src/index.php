<?php require 'config.php' ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" type="text/css">
    <base href="/">
</head>

<body class="text-center">
    <?php
    switch (explode("?", $_SERVER['REQUEST_URI'])[0]) {
        case '/login':
            require 'inc/login.php';
            include 'tpl/login.tpl.php';
            break;

        case '/signup':
            require 'inc/signup.php';
            include 'tpl/signup.tpl.php';
            break;
        case '/dashboard':
            require 'inc/dashboard.php';
            include 'tpl/dashboard.tpl.php';
            break;

        case '/home':
        case '/':
            require 'inc/home.php';
            include 'tpl/home.tpl.php';
            break;

        default:
            header('HTTP/1.0 404 Not Found');
            include 'tpl/page_not_found.tpl.php';
            break;
    }
    ?>
</body>

</html>