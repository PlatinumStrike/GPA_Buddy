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

function groupByTerm($transcript_data)
{
    $grouped = array();
    foreach ($transcript_data as $class) {
        $grouped[$class->term][] = $class;
    }
    uksort($grouped, function ($a, $b) {
        $months = ["Winter" => 0.25, "Spring/Summer" => .5, "Fall" => 0.75];
        $aYear = intval(substr($a, 0, 4)) + $months[substr($a, 5, strlen($a) - 5)];
        $bYear = intval(substr($b, 0, 4)) + $months[substr($b, 5, strlen($b) - 5)];
        return $aYear > $bYear;
    });
    return $grouped;
}

function classCard($classData)
{
    $gpaNum = [
        "A+" => 12,
        "A" => 11,
        "A-" => 10,
        "B+" => 9,
        "B" => 8,
        "B-" => 7,
        "C+" => 6,
        "C" => 5,
        "C-" => 4,
        "D+" => 3,
        "D" => 2,
        "D-" => 1,
        "F" => 0,
        "COM" => 0,
        "MT" => 0,
    ];

    $unitsEarned = $classData->status == 'Taken' ? $classData->units : "0.00";
    $gradePoints = floatval($unitsEarned) * ($gpaNum[$classData->grade] ?? 0);
    $totalGradePoints = floatval($unitsEarned) * 12;
    return "<div class='border-2'>" .
        "<h4>{$classData->course}</h4>" .
        "<h5>{$classData->description}</h5>" .
        "<h2>{$classData->grade}</h2>" .
        "<h5><b>{$gradePoints}</b>/{$totalGradePoints}</h5>" .
        "<h6>{$unitsEarned}/{$classData->units} Units</h6>" .
        "</div>";
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $transcript_data = json_decode(Database::selectQuery("SELECT transcript from transcripts where id=?", [$_SESSION['user_id']])['transcript'] ?? null);
    $transcript_terms = groupByTerm($transcript_data);

    $class_list = "";
    foreach ($transcript_terms as $term) {
        $class_list .= "<div class='collapsible'>" .
            "<div class='collapsible-header'><h6>{$term[0]->term}</h6>" .
            "</div>" .
            "<div class='collapsible-body'>";

        foreach ($term as $class) {
            $class_list .= classCard($class);
        }
        $class_list .= "</div></div>";
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    switch ($_POST['form_title']) {
        case "upload_transcript":
            $table = explode("\r\n", $_POST['incoming_transcript']);
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