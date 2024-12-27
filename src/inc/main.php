<?php
session_start();
// Router for include files, mostly for form submission and other external requests

switch ($_GET['path']) {
    case 'dashboard':
        include 'dashboard.php';
        break;

    case 'login':
        include 'login.php';
        break;

    case 'signup':
        include 'signup.php';
        break;

    default:
        break;
}