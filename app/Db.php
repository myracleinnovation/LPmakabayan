<?php
    require_once __DIR__ . '/Config.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    class Db
    {
        public static function getConnection() {
            try {  
                // Use configuration constants from Config.php
                $host = 'localhost';
                $dbname = 'MakabayanConstruction';
                $username = 'root';
                $password = '';

                $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                return $conn;
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        public static function testConnection() {
            try {
                $conn = self::getConnection();
                return [
                    'success' => true,
                    'message' => 'Database connection successful'
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Database connection failed: ' . $e->getMessage()
                ];
            }
        }
    }
?>