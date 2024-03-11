<?php

require_once(dirname(__DIR__, 1) . "/config.php");

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Webscraper
{
    var $client;

    function __construct()
    {
        $this->client = new HttpBrowser(HttpClient::create());
    }

    function getRequest($url)
    {
        $this->client->request("GET", $url);
    }

    function submitLoginForm($userid, $pwd, $targetUrl)
    {
        $this->client->submitForm('Submit', [
            'userid' => $userid,
            'pwd' => $pwd,
        ]);

        return $this->client->getCrawler()->getUri() == $targetUrl;
    }

    function getHTMLResponse()
    {
        return $this->client->getResponse()->getContent();
    }
}


class TableParser
{
    var $dom;
    var $table;
    var $header;
    var $gradeTable = array();

    function __construct($html)
    {
        libxml_use_internal_errors(true);
        $this->dom = new DOMDocument();
        $this->dom->loadHTML($html);
        libxml_clear_errors();
        $this->header = [];
        $this->gradeTable = [];
    }

    function getTranscriptData()
    {
        $x = new DOMXPath($this->dom);
        $this->table = $x->query("//table[@class='PSLEVEL1GRID']")->item(0);
        foreach ($this->table->childNodes as $rowNum => $row) {
            if ($row->nodeValue != "\n") {
                foreach ($row->childNodes as $i => $item) {
                    if ($rowNum == 0) {
                        break;
                    }
                    if ($rowNum == 1) {
                        if ($item->nodeValue != "\n") {
                            array_push($this->header, $item->nodeValue);
                        }
                    } else {
                        if ($item->nodeValue != "\n") {
                            $this->gradeTable[floor($rowNum / 2)][$this->header[floor($i / 2)]] = str_replace("\n", "", $item->nodeValue);
                            if ($i == 11) {
                                $this->gradeTable[floor($rowNum / 2)][$this->header[floor($i / 2)]] = $item->getElementsByTagName("img")->item(0)->getAttribute("alt");
                            }
                        }
                    }
                }
            }
        }
        return $this->gradeTable;
    }
}






// EOL