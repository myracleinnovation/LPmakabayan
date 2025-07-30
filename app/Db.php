<?php
    class Db {
        private static $isLive = true;
        public static function connect() {
            try {
                if (self::$isLive) {
                    $conn = new PDO("mysql:host=localhost;dbname=webmaster_;charset=utf8mb4", 'webmaster', 'h6p9c36Q&');
                } else {
                    $conn = new PDO("mysql:host=localhost;dbname=makabayanconstruction;charset=utf8mb4", 'root', '');
                }
                
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $conn;
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
    }
?>