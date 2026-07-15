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

    public function findAll(): array
    {
        $sql = '
            SELECT
                polls.id,
                polls.user_id,
                polls.title,
                polls.description,
                polls.expires_at,
                polls.created_at,
                users.name AS author_name,
                COUNT(DISTINCT options.id) AS options_count,
                COUNT(DISTINCT votes.id) AS votes_count
            FROM polls
            INNER JOIN users
                ON users.id = polls.user_id
            LEFT JOIN options
                ON options.poll_id = polls.id
            LEFT JOIN votes
                ON votes.poll_id = polls.id
            GROUP BY
                polls.id,
                polls.user_id,
                polls.title,
                polls.description,
                polls.expires_at,
                polls.created_at,
                users.name
            ORDER BY polls.created_at DESC
        ';

        $statement = $this->db->query($sql);

        return $statement->fetchAll();
    }
}