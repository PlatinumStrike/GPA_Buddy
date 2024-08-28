<?php
session_start();
require_once(dirname(__DIR__, 1) . "/config.php");
require_once("database.php");

require("navigation.php");
require_once("webscraper.php");

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
    "LWD" => 0,
    "P" => 0,
    "MT" => 0,
    "W" => 0,
];

$creditNoGPA = [
    "P"
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

// SIGNED-OUT CASE

if (!isset($_SESSION['user_id'])) {
    echo "<h1>Dashboard</h1>" .
        "<p>You are not currently logged in.</p>" .
        "<div><a href='/signup'><button>Sign Up</button></a> or " .
        "<a href='/login'><button>Login</button></a></div>";
    exit();
}

// AUTOLOAD TRANSCRIPT DATA IF CREDENTIALS ARE SAVED

$macCreds = Database::selectQuery("SELECT macid, macpwd from users where id=?", [$_SESSION['user_id']]);
ob_start();
include("./tpl/dashboard.credentials.tpl.php");
$mac_cred_form = ob_get_clean();

if ($macCreds["macid"] == "") {
    $transcript_form = $mac_cred_form;
} else {
    $webscraper = new Webscraper();
    $webscraper->getRequest("https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_MY_CRSEHIST.GBL");
    $login = $webscraper->submitLoginForm($macCreds["macid"], $macCreds["macpwd"], "https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_MY_CRSEHIST.GBL?");
    if (!$login) {
        redirect("/dashboard", ["MESSAGE" => "Credentials Incorrect"]);
        exit();
    }

    $tableParser = new TableParser($webscraper->getHTMLResponse());
    $data = $tableParser->getTranscriptData();

    // Push information to database and overwrite if neccessary
    Database::insertQuery("INSERT INTO transcripts (id, transcript, upload_date) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE transcript=?, upload_date=NOW()", [$_SESSION['user_id'], json_encode($data), json_encode($data)]);

    $transcript_form .= "<div class='collapsible m-10'><div class='collapsible-header m-10 p-4'>Your McMaster Credentials are saved. If you would like to update them, press <b>here</b></div>";
    $transcript_form .= "<div class='collapsible-body'>" . $mac_cred_form . "</div></div>";
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
    global $gpaNum, $creditNoGPA;
    $unitsEarned = (str_starts_with($classData->Status, 'Taken') != false && ($gpaNum[$classData->Grade] ?? 0) !== 0) ? $classData->Units : "0.00";
    $gradePointsPossible = floatval($unitsEarned) * 12;
    $gradePoints = floatval($unitsEarned) * ($gpaNum[$classData->Grade] ?? 0);
    if (in_array($classData->Grade, $creditNoGPA)) {
        $unitsEarned = str_starts_with($classData->Status, 'Taken') ? $classData->Units : "0.00";
        $gradePointsPossible = 0;
    }

    return "<div class='border-2 p-4'>" .
        "<h4>{$classData->Course}</h4>" .
        "<h5>{$classData->Description}</h5>" .
        "<h2>{$classData->Grade}</h2>" .
        "<h5><b>{$gradePoints}</b>/{$gradePointsPossible}</h5>" .
        "<h6>{$unitsEarned}/{$classData->Units} Units</h6>" .
        "</div>";
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    // Get Transcript Data if uploaded
    $transcript_data = Database::selectQuery("SELECT transcript, upload_date from transcripts where id=?", [$_SESSION['user_id']]);

    // Create empty variables in case of transcript lookup failure
    $transcript_upload_date = "<h6 class='inline px-4 py-2 rounded-xl text-red-700 bg-red-200'>Please upload a transcript</h6>";
    $transcript_uploaded = false;
    $class_list_length = "<h6 class='inline px-4 py-2 rounded-xl text-red-700 bg-red-200'>No classes loaded</h6>";
    $gradepoints_terms = [];
    $gradepoints_totals_terms = [];
    $class_list = "";
    $cGPA = "N/A";
    $cGPALetter = "No Transcript";
    $script = "";

    // Process transcript
    if ($transcript_data) {
        $transcript_upload_date = "<h6 class='inline px-4 py-2 rounded-xl text-green-700 bg-green-200'>Last uploaded: " . $transcript_data['upload_date'] . "</h6>";
        $transcript_uploaded = true;
        $transcript = (array) json_decode($transcript_data['transcript']);
        $class_list_length = "<h6 class='inline px-4 py-2 rounded-xl text-green-700 bg-green-200'>" . count($transcript) . " classes loaded</h6>";
        // Group and sort transcript by term
        $transcript_terms = groupByTerm($transcript);

        foreach ($transcript_terms as $term) {
            // Start Term GP counters
            $GP_earned = 0;
            $GP_possible = 0;
            $Units_possible = 0;

            // Begin UI for term
            $class_list .= "<div class='collapsible rounded-lg my-4 py-5 px-20'>" .
                "<div class='collapsible-header'><h3>{$term[0]->Term}</h3>" .
                "</div>" .
                "<div class='collapsible-body grid gap-4 grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4'>";

            foreach ($term as $class) {
                // Calculate Class GPs and apply to term information
                $units_earned = str_starts_with($class->Status, 'Taken') != false && ($gpaNum[$class->Grade] ?? 0) != 0 ? $class->Units : "0.00";
                $GP_earned += floatval($units_earned) * ($gpaNum[$class->Grade] ?? 0);
                // $class->Grade == "W" ? 0 : $units_earned;
                $GP_possible += in_array($class->Grade, $creditNoGPA) ? 0 : floatval($units_earned) * 12;
                $Units_possible += floatval($class->Units);

                // Add UI for class
                $class_list .= classCard($class);
            }

            // End UI for term
            $class_list .= "</div></div>";

            //Update 
            $GP_earned_terms[$term[0]->Term] = $GP_earned;
            $GP_possible_terms[$term[0]->Term] = $GP_possible;
            $Units_possible_terms[$term[0]->Term] = $Units_possible;
        }

        // Extract data for graphs
        $GP_earned_data = json_encode(array_values($GP_earned_terms));
        $GP_possible_data = json_encode(array_values($GP_possible_terms));
        $GP_possible_units = json_encode(array_values($Units_possible_terms));
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
        var unitLoadPerTerm = [
            {
                x: {$terms},
                y: {$GP_possible_units},
                name: 'Unit Load',
                type: 'bar'
            }
        ];
        Plotly.newPlot(document.getElementById('gpaTrendGraph'), pointsPerTerm, {barmode:'group', title: 'GPA Points per Term'});
        Plotly.newPlot(document.getElementById('gpaPercentTrendGraph'), percentagePointsPerTerm, {title: 'GPA Percentage per Term'});
        Plotly.newPlot(document.getElementById('unitTrendGraph'), unitLoadPerTerm, {title: 'Unit Load per Term'});
        ";

        // Compute Cummalitive GPAs and associated letter grade
        $cGPA = round(array_sum($GP_possible_terms) > 0 ? array_sum($GP_earned_terms) / array_sum($GP_possible_terms) * 12 : 0, 2);
        $cGPALetter = $gpaLetter[floor($cGPA)];
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Handle form POST requests
    switch ($_POST['form_title']) {
        case "upload_transcript":
            if (!isset($_POST['userid'])) {
                redirect("/dashboard", ["MESSAGE" => "Please provide a UserID"]);
                exit();
            }
            if (!isset($_POST['pwd'])) {
                redirect("/dashboard", ["MESSAGE" => "Please provide a Password"]);
                exit();
            }

            $_POST['userid'] = str_replace("@mcmaster.ca", "", $_POST['userid']);

            $webscraper = new Webscraper();
            $webscraper->getRequest("https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_MY_CRSEHIST.GBL");
            // $webscraper->getRequest("https://mosaic.mcmaster.ca/");
            $login = $webscraper->submitLoginForm($_POST['userid'], $_POST['pwd'], "https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_MY_CRSEHIST.GBL?");
            if (!$login) {
                redirect("/dashboard", ["MESSAGE" => "Credentials Incorrect"]);
                exit();
            }

            $tableParser = new TableParser($webscraper->getHTMLResponse());
            $data = $tableParser->getTranscriptData();

            // Push information to database and overwrite if neccessary
            Database::insertQuery("INSERT INTO transcripts (id, transcript, upload_date) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE transcript=?, upload_date=NOW()", [$_SESSION['user_id'], json_encode($data), json_encode($data)]);
            Database::insertQuery("UPDATE users SET macid=?, macpwd=? WHERE id=?", [$_POST['userid'], $_POST['pwd'], $_SESSION['user_id']]);

            // Redirect to newly populated dashboard
            redirect("/dashboard");
        default:
            break;
    }
}


// EOF