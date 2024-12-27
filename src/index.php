<?php
require 'config.php';
switch ($_GET['path']) {
    case '/db':
        include './adminer/adminer.php';
        break;

    case '/db/editor':
        include './adminer/editor-4.8.4.php';
        break;

    default:
        session_start();
        include 'tpl/main.tpl.php';
        break;
}
?>