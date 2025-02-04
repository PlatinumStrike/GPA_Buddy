<?php
require_once("navigation.php");
require_once("database.php");

// Autofill persisted email
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $email = $_GET['email'] ?? "";
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Check for valid emails
    if (empty(trim($_POST['email']))) {
        redirect("/login", ["MESSAGE" => "Please provide an email"]);
        exit();
    } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        redirect("/login", ["MESSAGE" => "Please provide a vaild email"]);
        exit();
    }

    // Check password
    if (empty(trim($_POST['pwd']))) {
        redirect("/signup", ["MESSAGE" => "Please provide a password", "email" => $_POST['email']]);
        exit();
    }

    // Check if user exists
    $result = Database::selectQuery("SELECT id, email, password FROM users WHERE email=?", [$_POST['email']]);
    if (!$result) {
        // CASE: Email Incorrect
        redirect("/login", ["MESSAGE" => "Incorrect email or password, please try again."]);
        exit();
    } else {
        // Verify password matches saved hash
        if (password_verify($_POST['pwd'], $result['password'])) {
            // Sign in user and send to dashboard
            if (!isset($_SESSION['user_id'])) {
                session_start();
            }
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['user_email'] = $_POST['email'];
            redirect("/dashboard");
            exit();
        } else {
            // CASE: Password Incorrect
            redirect("/login", ["MESSAGE" => "Incorrect email or password, please try again."]);
        }
    }
}

// EOF