<?php

namespace App\Models;

use PDO;

class User
{
    public function __construct(
        private PDO $db
    ) {
    }

    public function findByEmail(string $email): array|false
    {
        $sql = '
            SELECT id, name, email, password, created_at
            FROM users
            WHERE email = :email
            LIMIT 1
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'email' => $email
        ]);

        return $statement->fetch();
    }

    public function create(
        string $name,
        string $email,
        string $passwordHash
    ): int {
        $sql = '
            INSERT INTO users (name, email, password)
            VALUES (:name, :email, :password)
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash
        ]);

        return (int) $this->db->lastInsertId();
    }
}