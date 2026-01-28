<?php
namespace AppConfig;

use Dotenv\Dotenv;
use PDO;

class Database {
    // private static $db;
    
    public static function getConnection() {

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); 
        $dotenv->load();

        $DB_HOST = $_ENV["DB_HOST"];
        $DB_PORT = $_ENV["DB_PORT"];
        $DB_NAME = $_ENV["DB_NAME"];
        $DB_USER = $_ENV["DB_USER"];
        $DB_PASS = $_ENV["DB_PASS"];
        if(!isset($db)) {
            $db = new PDO("mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
        }
        return $db;
    }
}