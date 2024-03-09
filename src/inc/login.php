<?php

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (!empty($_GET['MESSAGE'])) {
        echo $_GET['MESSAGE'] . "<hr>";
    }
    $email = $_GET['email'] ?? "";
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (empty(trim($_POST['email']))) {
        $message = filter_var(str_replace(" ", "%20", "Please provide an email"), FILTER_SANITIZE_URL);
        header("Location: /signup?MESSAGE=" . $message);
        exit();
    } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $message = filter_var(str_replace(" ", "%20", "Please provide a vaild email"), FILTER_SANITIZE_URL);
        header("Location: /signup?MESSAGE=" . $message);
        exit();
    }

    if (empty(trim($_POST['pwd']))) {
        $message = filter_var(str_replace(" ", "%20", "Please provide a password"), FILTER_SANITIZE_URL);
        header("Location: /signup?email=" . $_POST['email'] . "&MESSAGE=" . $message);
        exit();
    }
    require_once("database.php");
    $conn = Database::getConnection();
    $sql = "SELECT * FROM users WHERE email=?";
    $result = $conn->execute_query($sql, [$_POST['email']])->fetch_assoc();
    if (!$result) {
        $message = filter_var(str_replace(" ", "%20", "Email incorrect, please try again."), FILTER_SANITIZE_URL);
        header("Location: /signup?email=" . $_POST['email'] . "&MESSAGE=" . $message);
        exit();
    } else {
        $pass = password_verify($_POST['pwd'], $result['password']);

        $_SESSION['user_id'] = $result['id'];
        $_SESSION['user_email'] = $_POST['email'];
        header("Location: /dashboard");
        exit();
    }
}
