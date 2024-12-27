<?php

require_once(dirname(__DIR__, 1) . "/config.php");

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Webscraper
{
    var $client;
    var $mosaicHome = "https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/NUI_FRAMEWORK.PT_LANDINGPAGE.GBL?";
    function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    function getRequest($url)
    {
        $this->client->request("GET", $url);
    }

    function submitLoginForm($userid, $pwd, $button_name = 'Submit', $field_user = 'userid', $field_pwd = 'pwd')
    {
        $this->client->submitForm($button_name, [
            $field_user => $userid,
            $field_pwd => $pwd,
        ]);

        return $this->client->getCrawler()->getUri() == $this->mosaicHome;
    }

    function getHTMLResponse()
    {
        return $this->client->getResponse()->getContent();
    }

    function login($userid, $pwd, $button_name = 'Submit', $field_user = 'userid', $field_pwd = 'pwd')
    {
        $this->getRequest("https://csprd.mcmaster.ca/psp/prcsprd/?cmd=login");
        $isLoggedIn = $this->submitLoginForm($userid, $pwd);
        if (!$isLoggedIn)
            return ["MESSAGE" => "Credentials Incorrect", "STATUS" => false];
        else
            return ["MESSAGE" => "Successfully Logged In", "STATUS" => true];
    }

    function getTranscript($userid, $pwd)
    {
        $return = $this->login($userid, $pwd);
        if (!$return['STATUS'])
            return $return;

        $this->getRequest("https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_MY_CRSEHIST.GBL");

        $html = $this->getHTMLResponse();
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $header = [];
        $gradeTable = [];

        $table = $xpath->query("//table[@class='PSLEVEL1GRID']")->item(0);
        foreach ($table->childNodes as $rowNum => $row) {
            if ($row->nodeValue != "\n") {
                foreach ($row->childNodes as $i => $item) {
                    if ($rowNum == 0) {
                        break;
                    }
                    if ($rowNum == 1) {
                        if ($item->nodeValue != "\n") {
                            array_push($header, $item->nodeValue);
                        }
                    } else {
                        if ($item->nodeValue != "\n") {
                            $gradeTable[floor($rowNum / 2)][$header[floor($i / 2)]] = str_replace("\n", "", $item->nodeValue);
                            if ($i == 11) {
                                $gradeTable[floor($rowNum / 2)][$header[floor($i / 2)]] = $item->getElementsByTagName("img")->item(0)->getAttribute("alt");
                            }
                        }
                    }
                }
            }
        }

        $return['CONTENT'] = $gradeTable;
        $return['STATUS'] = true;
        $return['MESSAGE'] = "Successfully parsed the transcript data.";
        return $return;
    }

    function getTimetable($userid, $pwd)
    {
        $return = $this->login($userid, $pwd);
        if (!$return['STATUS'])
            return $return;

        $this->getRequest("https://csprd.mcmaster.ca/psc/prcsprd/EMPLOYEE/SA/c/SA_LEARNER_SERVICES.SSS_STUDENT_CENTER.GBL");

        $html = $this->getHTMLResponse();
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Initialize an array to hold the timetable data
        $timetableData = [];

        // Use XPath to find the table containing the schedule
        $table = $xpath->query("//table[@id='STDNT_WEEK_SCHD\$scroll\$0']")->item(0);
        if (!$table) {
            return ['STATUS' => false, 'MESSAGE' => 'Table not found'];
        }

        // Get rows from the table (excluding headers)
        $rows = $xpath->query("tr[2]//tr[position() > 1]", $table);

        foreach ($rows as $rowIndex => $row) {
            $classData = [];

            // Columns in a typical timetable
            $columns = $xpath->query("td", $row);

            // Extract class name, instructor, room number, and dates
            foreach ($columns as $colIndex => $cell) {
                switch ($colIndex) {
                    case 0: // Component Info
                        $cell = explode("\r", trim($cell->textContent));

                        $classData['CODE'] = explode("-", $cell[0])[0];
                        $classData['SECTION'] = explode("-", $cell[0])[1];
                        $classData['TYPE'] = explode(" ", $cell[1])[0];
                        $classData['NUMBER'] = intval(substr(explode(" ", $cell[1])[1], 1, -1));
                        break;
                    case 1: // Time Info
                        $cell = explode("\r", trim($cell->textContent));
                        $classData['DAYS'] = explode(" ", $cell[0])[0];
                        $times = array_slice(explode(" ", $cell[0]), 1);
                        $classData['TIME'] = ["START" => $times[0], "END" => $times[2]];
                        $classData['LOCATION'] = ["BUILDING" => explode(" ", $cell[1])[0], "ROOM" => explode(" ", $cell[1])[1]];
                        break;
                }
            }

            // Add the class data to the timetable
            if (!empty($classData)) {
                $timetableData[] = $classData;
            }
        }

        $return['STATUS'] = true;
        $return['MESSAGE'] = "Timetable retrieved successfully";
        $return['CONTENT'] = $timetableData[0];
        return $return;
    }

}

// EOF