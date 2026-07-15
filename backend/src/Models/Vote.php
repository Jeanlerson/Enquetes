<?php

namespace App\Models;

use PDO;

class Vote
{
    public function __construct(
        private PDO $db
    ) {
    }

    public function hasUserVoted(
        int $pollId,
        int $userId
    ): bool {
        $sql = '
            SELECT id
            FROM votes
            WHERE poll_id = :poll_id
              AND user_id = :user_id
            LIMIT 1
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'poll_id' => $pollId,
            'user_id' => $userId
        ]);

        return (bool) $statement->fetch();
    }

    public function optionBelongsToPoll(
        int $optionId,
        int $pollId
    ): bool {
        $sql = '
            SELECT id
            FROM options
            WHERE id = :option_id
              AND poll_id = :poll_id
            LIMIT 1
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'option_id' => $optionId,
            'poll_id' => $pollId
        ]);

        return (bool) $statement->fetch();
    }

    public function create(
        int $pollId,
        int $optionId,
        int $userId
    ): int {
        $sql = '
            INSERT INTO votes (
                poll_id,
                option_id,
                user_id
            )
            VALUES (
                :poll_id,
                :option_id,
                :user_id
            )
        ';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'poll_id' => $pollId,
            'option_id' => $optionId,
            'user_id' => $userId
        ]);

        return (int) $this->db->lastInsertId();
    }
}