<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $host = getenv('DB_HOST');
            $dbname = getenv('DB_NAME');
            $user = getenv('DB_USER');
            $password = getenv('DB_PASS');

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

            try {
                self::$connection = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new PDOException("Erro na conexão com o banco: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}