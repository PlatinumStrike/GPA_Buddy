<?php

$conn = new mysqli($_SERVER['db_path'], $_SERVER['db_user'], $_SERVER['db_pass']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
