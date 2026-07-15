<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $host = trim($_ENV['DB_HOST'] ?? 'localhost');
            $port = trim($_ENV['DB_PORT'] ?? '3306');
            $dbname = trim($_ENV['DB_NAME'] ?? '');
            $user = trim($_ENV['DB_USER'] ?? '');
            $password = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
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