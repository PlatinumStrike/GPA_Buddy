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

/**
 * Sorts incoming transcript data by term
 * @param mixed $transcript_data Unsorted Array of class list
 * @return array Array of term-sorted class list
 */
function groupByTerm($transcript_data)
{
    // Sort by term name
    $grouped = array();
    foreach ($transcript_data as $class) {
        $grouped[$class->Term][] = $class;
    }

    // Sort term seasons
    uksort($grouped, function ($a, $b) {
        $months = ["Winter" => 0.25, "Spring/Summer" => .5, "Fall" => 0.75];
        $aYear = intval(substr($a, 0, 4)) + $months[substr($a, 5, strlen($a) - 5)];
        $bYear = intval(substr($b, 0, 4)) + $months[substr($b, 5, strlen($b) - 5)];
        return $aYear > $bYear;
    });
    return $grouped;
}

/**
 * Generate an HTML classCard
 * @param mixed $classData Object containing class grade/status data
 * @return string HTML Displaying class data
 */
function classCard($classData)
{
    global $gpaNum;
    $unitsEarned = $classData->Status == 'Taken' ? $classData->Units : "0.00";
    $gradePoints = floatval($unitsEarned) * ($gpaNum[$classData->Grade] ?? 0);
    $totalGradePoints = floatval($unitsEarned) * 12;

    return "<div class='border-2 my-4'>" .
        "<h4>{$classData->Course}</h4>" .
        "<h5>{$classData->Description}</h5>" .
        "<h2>{$classData->Grade}</h2>" .
        "<h5><b>{$gradePoints}</b>/{$totalGradePoints}</h5>" .
        "<h6>{$unitsEarned}/{$classData->Units} Units</h6>" .
        "</div>";
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    // Get Transcript Data if uploaded
    $transcript_data = Database::selectQuery("SELECT transcript, upload_date from transcripts where id=?", [$_SESSION['user_id']]);

    // Create empty variables in case of transcript lookup failure
    $transcript_upload_date = "<h6 class='inline px-4 py-2 rounded-xl text-red-700 bg-red-200'>Please upload a transcript</h6>";
    $gradepoints_terms = [];
    $gradepoints_totals_terms = [];
    $class_list = "";
    $cGPA = "N/A";
    $cGPALetter = "No Transcript";

    // Process transcript
    if ($transcript_data) {
        $transcript_upload_date = "<h6 class='inline px-4 py-2 rounded-xl text-green-700 bg-green-200'>Last uploaded: " . $transcript_data['upload_date'] . "</h6>";
        $transcript = json_decode($transcript_data['transcript']);
        // Group and sort transcript by term
        $transcript_terms = groupByTerm($transcript);

        foreach ($transcript_terms as $term) {
            // Start Term GP counters
            $GP_earned = 0;
            $GP_possible = 0;

            // Begin UI for term
            $class_list .= "<div class='collapsible'>" .
                "<div class='collapsible-header'><h6>{$term[0]->Term}</h6>" .
                "</div>" .
                "<div class='collapsible-body'>";

            foreach ($term as $class) {
                // Calculate Class GPs and apply to term information
                $units_earned = $class->Status == 'Taken' ? $class->Units : "0.00";
                $GP_earned += floatval($units_earned) * ($gpaNum[$class->Grade] ?? 0);
                $GP_possible  += floatval($units_earned) * 12;

                // Add UI for class
                $class_list .= classCard($class);
            }

            // End UI for term
            $class_list .= "</div></div>";

            //Update 
            $GP_earned_terms[$term[0]->Term] = $GP_earned;
            $GP_possible_terms[$term[0]->Term] = $GP_possible;
        }

        // Extract data for graphs
        $GP_earned_data = json_encode(array_values($GP_earned_terms));
        $GP_possible_data = json_encode(array_values($GP_possible_terms));
        $terms = json_encode(array_keys($GP_earned_terms));

        $percent_earned_data = json_encode(array_map(function ($a, $b) {
            return $b > 0 ? round($a / $b * 100, 1) . "%" : 0;
        }, $GP_earned_terms, $GP_possible_terms));

        $letter_earned_data = json_encode(array_map(function ($a, $b) {
            global $gpaLetter;
            return ($gpaLetter[$b > 0 ? floor($a / ($b / 12)) : 0]) . ", " . ($b > 0 ? round($a / $b * 12, 2) : 0);
        }, $GP_earned_terms, $GP_possible_terms));

        // Define graph driver code
        $script = "var pointsPerTerm = [
            {
                x: {$terms},
                y: {$GP_earned_data},
                name: 'Points Earned',
                type: 'bar'
            },
            {
                x: {$terms},
                y: {$GP_possible_data},
                name: 'Total Points',
                type: 'bar'
            },
        ];
        var percentagePointsPerTerm = [
            {
                x: {$terms},
                y: {$percent_earned_data},
                text: {$letter_earned_data},
                name: 'Percentage Earned',
                type: 'bar'
            }
        ];
        Plotly.newPlot(document.getElementById('gpaTrendGraph'), pointsPerTerm, {barmode:'group', title: 'GPA Points per Term'});
        Plotly.newPlot(document.getElementById('gpaPercentTrendGraph'), percentagePointsPerTerm, {title: 'GPA Percentage per Term'});
        ";

        // Compute Cummalitive GPAs and associated letter grade
        $cGPA = array_sum($GP_possible_terms) > 0 ? array_sum($GP_earned_terms) / array_sum($GP_possible_terms) * 12 : 0;
        $cGPALetter = $gpaLetter[floor($cGPA)];
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Handle form POST requests
    switch ($_POST['form_title']) {
        case "upload_transcript":
            // Check input table header for input validity
            if (!str_starts_with($_POST['incoming_transcript'], "Course	Description	Term	Grade	Units	Status")) {
                redirect("/dashboard", ["MESSAGE" => "Please provide a valid transcript"]);
                die();
            }

            // Parse table on rows
            $table = explode("\r\n", $_POST['incoming_transcript']);

            // Extract header and seperate on tab
            $header = explode("\t", $table[0]);

            // Remove header from data
            array_shift($table);

            // Chunk data into rows
            $data = array_chunk($table, 6);

            // Map data into objects
            $data = array_map(function ($obj) {
                global $header;
                return [
                    $header[0] => $obj[0],
                    $header[1] => $obj[1],
                    $header[2] => $obj[2],
                    $header[3] => $obj[3],
                    $header[4] => $obj[4],
                    $header[5] => $obj[5],
                ];
            }, $data);

            // Push information to database and overwrite if neccessary
            Database::insertQuery("INSERT INTO transcripts (id, transcript) VALUES (?,?) ON DUPLICATE KEY UPDATE transcript=?", [$_SESSION['user_id'], json_encode($data), json_encode($data)]);

            // Redirect to newly populated dashboard
            redirect("/dashboard");
        default:
            break;
    }
}


// EOF