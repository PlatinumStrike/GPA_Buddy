<?php
require 'config.php';
switch (explode("?", $_SERVER['REQUEST_URI'])[0]) {
    case '/db':
        include './adminer/adminer.php';
        break;

    case '/db/editor':
        include './adminer/editor-4.8.4.php';
        break;

    default:
        include 'inc/home.php';
        include 'tpl/main.tpl.php';
        break;
}
?>