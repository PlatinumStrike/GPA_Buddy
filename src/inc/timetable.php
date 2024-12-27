<?php
require_once(dirname(__DIR__, 1) . "/config.php");
require_once("database.php");
require("navigation.php");
require_once("webscraper.php");

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    // AUTOLOAD TRANSCRIPT DATA IF CREDENTIALS ARE SAVED

    $macCreds = Database::selectQuery("SELECT macid, macpwd from users where id=?", [$_SESSION['user_id']]);
    ob_start();
    include("./tpl/timetable.credentials.tpl.php");
    $mac_cred_form = ob_get_clean();

    if ($macCreds["macid"] == "") {
        $cred_form = $mac_cred_form;
    } else {
        $webscraper = new Webscraper();

        $webscraper->getRequest("https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_MY_CRSEHIST.GBL");
        $login = $webscraper->submitLoginForm($macCreds["macid"], $macCreds["macpwd"], "https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_MY_CRSEHIST.GBL?");

        $webscraper->getRequest("https://mytimetable.mcmaster.ca/criteria.jsp");
        echo $webscraper->getHTMLResponse();
        // $login = $webscraper->submitLoginForm($macCreds["macid"], $macCreds["macpwd"], "https://mytimetable.mcmaster.ca/criteria.jsp", "login", "word1", "word2");
        // if (!$login) {
        //     redirect("/timetable", ["MESSAGE" => "Credentials Incorrect"]);
        //     exit();
        // }
        // $webscraper->getRequest("https://mytimetable.mcmaster.ca/criteria.jsp");
        // $tableParser = new TableParser($webscraper->getHTMLResponse());
        // echo $webscraper->getHTMLResponse();
        // $data = $tableParser->getTranscriptData();

        // Push information to database and overwrite if neccessary
        // Database::insertQuery("INSERT INTO transcripts (id, transcript, upload_date) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE transcript=?, upload_date=NOW()", [$_SESSION['user_id'], json_encode($data), json_encode($data)]);

        $cred_form .= "<div class='collapsible m-10'><div class='collapsible-header m-10 p-4'>Your McMaster Credentials are saved. If you would like to update them, press <b>here</b></div>";
        $cred_form .= "<div class='collapsible-body'>" . $mac_cred_form . "</div></div>";
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {

}

// EOF