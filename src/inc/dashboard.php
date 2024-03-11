<?php

require_once(dirname(__DIR__, 1) . "/config.php");
require_once("database.php");

require("navigation.php");

$transcript_data = "";

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

$gpaLetter = [
    12 => "A+",
    11 => "A",
    10 => "A-",
    9 => "B+",
    8 => "B",
    7 => "B-",
    6 => "C+",
    5 => "C",
    4 => "C-",
    3 => "D+",
    2 => "D",
    1 => "D-",
    0 => "F",
];

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
    global $gpaNum;
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

    $gradepoints_terms = [];
    $gradepoints_totals_terms = [];
    $class_list = "";

    foreach ($transcript_terms as $term) {
        $gradepoints = 0;
        $gradepoints_totals = 0;
        $class_list .= "<div class='collapsible'>" .
            "<div class='collapsible-header'><h6>{$term[0]->term}</h6>" .
            "</div>" .
            "<div class='collapsible-body'>";

        foreach ($term as $class) {
            $unitsEarned = $class->status == 'Taken' ? $class->units : "0.00";
            $gpts = floatval($unitsEarned) * ($gpaNum[$class->grade] ?? 0);
            $gpts_total = floatval($unitsEarned) * 12;
            $gradepoints += $gpts;
            $gradepoints_totals += $gpts_total;
            $class_list .= classCard($class);
        }
        $class_list .= "</div></div>";
        $gradepoints_terms[$term[0]->term] = $gradepoints;
        $gradepoints_totals_terms[$term[0]->term] = $gradepoints_totals;
    }

    $pointsEarned = json_encode(array_values($gradepoints_terms));
    $percentagePointsEarned = json_encode(array_map(function ($a, $b) {
        return $b > 0 ? round($a / $b * 100, 1) . "%" : 0;
    }, $gradepoints_terms, $gradepoints_totals_terms));
    $letterPointsEarned = json_encode(array_map(function ($a, $b) {
        global $gpaLetter;
        return ($gpaLetter[$b > 0 ? floor($a / ($b / 12)) : 0]) . ", " . ($b > 0 ? round($a / $b * 12, 2) : 0);
    }, $gradepoints_terms, $gradepoints_totals_terms));
    $totalPoints = json_encode(array_values($gradepoints_totals_terms));
    $terms = json_encode(array_keys($gradepoints_terms));
    $script = "var pointsPerTerm = [
        {
            x: {$terms},
            y: {$pointsEarned},
            name: 'Points Earned',
            type: 'bar'
        },
        {
            x: {$terms},
            y: {$totalPoints},
            name: 'Total Points',
            type: 'bar'
        },
    ];
    var percentagePointsPerTerm = [
        {
            x: {$terms},
            y: {$percentagePointsEarned},
            text: {$letterPointsEarned},
            name: 'Percentage Earned',
            type: 'bar'
        }
    ];
    Plotly.newPlot(document.getElementById('gpaTrendGraph'), pointsPerTerm, {barmode:'group', title: 'GPA Points per Term'});
    Plotly.newPlot(document.getElementById('gpaPercentTrendGraph'), percentagePointsPerTerm, {title: 'GPA Percentage per Term'});
    ";

    $cGPA = array_sum($gradepoints_totals_terms) > 0 ? array_sum($gradepoints_terms) / array_sum($gradepoints_totals_terms) * 12 : 0;
    $cGPALetter = $gpaLetter[floor($cGPA)];
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    switch ($_POST['form_title']) {
        case "upload_transcript":
            if (!str_starts_with($_POST['incoming_transcript'], "Course	Description	Term	Grade	Units	Status")) {
                redirect("/dashboard", ["MESSAGE" => "Please provide a valid transcript"]);
                die();
            }
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