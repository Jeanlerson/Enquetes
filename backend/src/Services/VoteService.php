<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\Vote;
use InvalidArgumentException;
use RuntimeException;

class VoteService
{
    public function __construct(
        private Vote $voteModel,
        private Poll $pollModel
    ) {
    }

    public function vote(
        int $pollId,
        int $userId,
        array $data
    ): array {
        if ($pollId <= 0) {
            throw new InvalidArgumentException(
                'O identificador da enquete é inválido.'
            );
        }

        $optionId = (int) ($data['option_id'] ?? 0);

        if ($optionId <= 0) {
            throw new InvalidArgumentException(
                'A opção de voto é obrigatória.'
            );
        }

        $poll = $this->pollModel->findById($pollId);

        if (!$poll) {
            throw new RuntimeException(
                'Enquete não encontrada.'
            );
        }

        if (
            $poll['expires_at'] !== null
            && new \DateTime($poll['expires_at']) <= new \DateTime()
        ) {
            throw new RuntimeException(
                'Esta enquete já expirou.'
            );
        }

        if (
            !$this->voteModel->optionBelongsToPoll(
                $optionId,
                $pollId
            )
        ) {
            throw new InvalidArgumentException(
                'A opção informada não pertence a esta enquete.'
            );
        }

        if (
            $this->voteModel->hasUserVoted(
                $pollId,
                $userId
            )
        ) {
            throw new RuntimeException(
                'Você já votou nesta enquete.'
            );
        }

        $voteId = $this->voteModel->create(
            $pollId,
            $optionId,
            $userId
        );

        return [
            'id' => $voteId,
            'poll_id' => $pollId,
            'option_id' => $optionId,
            'user_id' => $userId
        ];
    }
}