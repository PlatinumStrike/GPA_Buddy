<?php

require_once(dirname(__DIR__, 1) . "/config.php");
class Database
{

    private static $db;
    private $conn;

    private function __construct()
    {
        $this->conn = new mysqli($_SERVER['db_path'], $_SERVER['db_user'], $_SERVER['db_pass'], $_SERVER['db_name']);
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    function __destruct()
    {
        $this->conn->close();
    }

    public static function getConnection()
    {
        if (self::$db == null) {
            self::$db = new Database();
        }
        return self::$db->conn;
    }

    public static function selectQuery($query, $parameters)
    {
        $conn = self::getConnection();
        return $conn->execute_query($query, $parameters)->fetch_assoc();
    }

    public static function insertQuery($query, $parameters)
    {
        $conn = self::getConnection();
        return $conn->execute_query($query, $parameters);
    }
}
