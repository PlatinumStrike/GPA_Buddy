<?php require 'config.php' ?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/css/tailwind.css" type="text/css">
    <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" type="text/css">
    <link rel="stylesheet" href="/css/main.css" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/">
    <?php
    switch (explode("?", $_SERVER['REQUEST_URI'])[0]) {
        case '/dashboard':
            echo "<link rel='stylesheet' href='/css/collapsible.css' type='text/css'>";
            echo "<script src='/js/collapsible.js' charset='utf-8'></script>";
            echo "<script src='/js/plotly-2.29.1.min.js' charset='utf-8'></script>";
            break;
        default:
            break;
    }
    ?>
</head>

<body class="text-center">
    <?php

    // Display messages to UI if applicable
    if (!empty($_GET['MESSAGE'])) {
        echo $_GET['MESSAGE'] . "<hr>";
    }

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

        case '404':
        default:
            header('HTTP/1.0 404 Not Found');
            include 'tpl/page_not_found.tpl.php';
            break;
    }
    ?>
</body>

</html>