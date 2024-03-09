<?php

// Load composer dependencies

define('DIR_VENDOR', __DIR__ . '/vendor/');

if (file_exists(DIR_VENDOR . 'autoload.php')) {
    require_once(DIR_VENDOR . 'autoload.php');
}


// Load environment file

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

//EOF
