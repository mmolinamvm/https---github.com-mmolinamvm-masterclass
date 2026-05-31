<?php
namespace config;

use PDO;
use PDOException;

class Database {
    private static $host = 'localhost';
    private static $db   = 'masterclass_db';
    private static $user = 'masterclass_user';
    private static $pass = 'ContrasenyaSegura123!';
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8mb4",
                    self::$user,
                    self::$pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Error de connexió: " . $e->getMessage()]);
                exit;
            }
        }
        return self::$conn;
    }
}