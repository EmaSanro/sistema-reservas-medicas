<?php
namespace AppConfig;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database {
    private static $db = null;
    
    public static function getConnection() {
        if(self::$db == null) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); 
            $dotenv->load();
    
            $DB_HOST = $_ENV["DB_HOST"];
            $DB_PORT = $_ENV["DB_PORT"];
            $DB_NAME = $_ENV["DB_NAME"];
            $DB_USER = $_ENV["DB_USER"];
            $DB_PASS = $_ENV["DB_PASS"];
            try {
                self::$db = new PDO("mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
            } catch (\Throwable $th) {
                throw new PDOException("Error al establecer conexion" . $th->getMessage());
            }
        }
        return self::$db;
    }
}