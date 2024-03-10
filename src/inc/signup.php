<?php

// TODO: Add "Keep me signed in" feature

require_once("navigation.php");
require_once("database.php");

// Autofill persisted email
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $email = $_GET['email'] ?? "";
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Check for valid emails
    if (empty(trim($_POST['email']))) {
        redirect("/signup", ["MESSAGE" =>  "Please provide an email"]);
        exit();
    } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        redirect("/signup", ["MESSAGE" => "Please provide a vaild email"]);
        exit();
    }

    // Check for existing user
    $result = Database::selectQuery("SELECT id FROM users WHERE email = ?", [$_POST['email']]);
    if (isset($result) && sizeof($result) >= 1) {
        redirect("/signup", ["MESSAGE" => "An account already exists under this email, please <a href='/login'>login</a>"]);
        exit();
    }

    // Check password
    if (empty(trim($_POST['pwd']))) {
        redirect("/signup", ["MESSAGE" => "Please provide a password", "email" => $_POST['email']]);
        exit();
    }

    // Add new user and send to dashboard
    $result = Database::insertQuery("INSERT INTO users (email, password) VALUES (?, ?)", [$_POST['email'], password_hash($_POST['pwd'], PASSWORD_DEFAULT)]);
    if (!$result) {
        redirect("/signup", ["MESSAGE" => "An error occurred, please try again later.", "email" => $_POST['email']]);
        exit();
    } else {
        $result = Database::selectQuery("SELECT id FROM users WHERE email = ?", [$_POST['email']]);
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['user_email'] = $_POST['email'];
        redirect("/dashboard");
        exit();
    }
}

// EOF