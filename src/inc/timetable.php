<?php
require_once(dirname(__DIR__, 1) . "/config.php");
require_once("database.php");
require("navigation.php");
require_once("webscraper.php");

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    // AUTOLOAD TRANSCRIPT DATA IF CREDENTIALS ARE SAVED

    $macCreds = Database::selectQuery("SELECT macid, macpwd from users where id=?", [$_SESSION['user_id']]);
    ob_start();
    include("./tpl/forms/form.credentials.tpl.php");
    $mac_cred_form = ob_get_clean();

    if ($macCreds["macid"] == "") {
        $cred_form = $mac_cred_form;
    } else {
        $webscraper = new Webscraper();
        $response = $webscraper->getTimetable($macCreds["macid"], $macCreds["macpwd"]);
        if (!$response['STATUS']) {
            redirect("/timetable", ["MESSAGE" => $response['MESSAGE']]);
            exit();
        }
        $data = $response['CONTENT'];
        $timetable = json_encode($data);
        foreach ($data as $row_data) {
            $query =
                "INSERT INTO components (year, semester, course_code, section, component_number, type, days, start_time, end_time, location) VALUES (?,?,?,?,?,?,?,?,?,?)" .
                "ON DUPLICATE KEY UPDATE " .
                "year = VALUES(year)," .
                "semester = VALUES(semester)," .
                "course_code = VALUES(course_code)," .
                "section = VALUES(section)," .
                "type = VALUES(type)," .
                "days = VALUES(days)," .
                "start_time = VALUES(start_time)," .
                "end_time = VALUES(end_time)," .
                "location = VALUES(location)";
            $sql_data = [$row_data['year'], $row_data['semester'], $row_data['course_code'], $row_data['section'], $row_data['component_number'], $row_data['type'], $row_data['days'], $row_data['start_time'], $row_data['end_time'], $row_data['location']];
            Database::insertQuery($query, $sql_data);
        }
        $cred_form = "<div class='collapsible m-10'><div class='collapsible-header m-10 p-4'>Your McMaster Credentials are saved. If you would like to update them, press <b>here</b></div>";
        $cred_form .= "<div class='collapsible-body'>" . $mac_cred_form . "</div></div>";
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {

}

// EOF