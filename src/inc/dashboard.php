<?php

require_once(dirname(__DIR__, 1) . "/config.php");
require_once("database.php");

$transcript_data = "";

require("navigation.php");

if (!isset($_SESSION['user_id'])) {
    echo "<h1>Dashboard</h1>" .
        "<p>You are not currently logged in.</p>" .
        "<div><a href='/signup'><button>Sign Up</button></a> or " .
        "<a href='/login'><button>Login</button></a></div>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $transcript_data = json_decode(Database::selectQuery("SELECT transcript from transcripts where id=?", [$_SESSION['user_id']])['transcript'] ?? null);
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    switch ($_POST['form_title']) {
        case "upload_transcript":
            $table = explode("\n", $_POST['incoming_transcript']);
            $header = explode("\t", $table[0]);
            array_shift($table);
            $data = array_chunk($table, 6);
            $data = array_map(function ($obj) {
                return [
                    "course" => $obj[0],
                    "description" => $obj[1],
                    "term" => $obj[2],
                    "grade" => $obj[3],
                    "units" => $obj[4],
                    "status" => $obj[5],
                ];
            }, $data);
            Database::insertQuery("INSERT INTO transcripts (id, transcript) VALUES (?,?) ON DUPLICATE KEY UPDATE transcript=?", [$_SESSION['user_id'], json_encode($data), json_encode($data)]);
            redirect("/dashboard");
        default:
            break;
    }
}


// EOF