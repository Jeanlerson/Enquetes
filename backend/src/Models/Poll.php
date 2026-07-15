<?php

namespace App\Models;

use PDO;
use Throwable;

class Poll
{
    public function __construct(
        private PDO $db
    ) {
    }

    public function create(
        int $userId,
        string $title,
        ?string $description,
        ?string $expiresAt,
        array $options
    ): int {
        try {
            $this->db->beginTransaction();

            $pollSql = '
                INSERT INTO polls (
                    user_id,
                    title,
                    description,
                    expires_at
                )
                VALUES (
                    :user_id,
                    :title,
                    :description,
                    :expires_at
                )
            ';

            $statement = $this->db->prepare($pollSql);
            $statement->execute([
                'user_id' => $userId,
                'title' => $title,
                'description' => $description,
                'expires_at' => $expiresAt
            ]);

            $pollId = (int) $this->db->lastInsertId();

            $optionSql = '
                INSERT INTO options (poll_id, option_text)
                VALUES (:poll_id, :option_text)
            ';

            $optionStatement = $this->db->prepare($optionSql);

            foreach ($options as $option) {
                $optionStatement->execute([
                    'poll_id' => $pollId,
                    'option_text' => $option
                ]);
            }

            $this->db->commit();

            return $pollId;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $error;
        }
    }
}