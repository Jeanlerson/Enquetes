<?php

namespace App\Services;

use App\Models\Poll;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

class PollService
{
    public function __construct(
        private Poll $pollModel
    ) {
    }

    public function create(array $data, int $userId): array
    {
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $expiresAt = trim($data['expires_at'] ?? '');
        $options = $data['options'] ?? [];

        if ($title === '') {
            throw new InvalidArgumentException(
                'O título da enquete é obrigatório.'
            );
        }

        if (!is_array($options)) {
            throw new InvalidArgumentException(
                'As opções devem ser enviadas como uma lista.'
            );
        }

        $options = array_values(
            array_filter(
                array_map(
                    fn ($option) => trim((string) $option),
                    $options
                ),
                fn ($option) => $option !== ''
            )
        );

        if (count($options) < 2 || count($options) > 8) {
            throw new InvalidArgumentException(
                'A enquete deve possuir entre 2 e 8 opções.'
            );
        }

        if (count($options) !== count(array_unique($options))) {
            throw new InvalidArgumentException(
                'A enquete não pode possuir opções repetidas.'
            );
        }

        if ($expiresAt !== '') {
            $expirationDate = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $expiresAt
            );

            if (
                !$expirationDate ||
                $expirationDate->format('Y-m-d H:i:s') !== $expiresAt
            ) {
                throw new InvalidArgumentException(
                    'A data de expiração deve usar o formato Y-m-d H:i:s.'
                );
            }

            if ($expirationDate <= new DateTime()) {
                throw new InvalidArgumentException(
                    'A data de expiração deve estar no futuro.'
                );
            }
        }

        $pollId = $this->pollModel->create(
            $userId,
            $title,
            $description !== '' ? $description : null,
            $expiresAt !== '' ? $expiresAt : null,
            $options
        );

        return [
            'id' => $pollId,
            'user_id' => $userId,
            'title' => $title,
            'description' => $description !== ''
                ? $description
                : null,
            'expires_at' => $expiresAt !== ''
                ? $expiresAt
                : null,
            'options' => $options
        ];
    }

    public function getAll(): array
    {
        $polls = $this->pollModel->findAll();

        return array_map(
            function (array $poll): array {
                return [
                    'id' => (int) $poll['id'],
                    'title' => $poll['title'],
                    'description' => $poll['description'],
                    'expires_at' => $poll['expires_at'],
                    'created_at' => $poll['created_at'],
                    'author' => [
                        'id' => (int) $poll['user_id'],
                        'name' => $poll['author_name']
                    ],
                    'options_count' => (int) $poll['options_count'],
                    'votes_count' => (int) $poll['votes_count'],
                    'is_expired' => $this->isExpired(
                        $poll['expires_at']
                    )
                ];
            },
            $polls
        );
    }

    private function isExpired(?string $expiresAt): bool
    {
        if ($expiresAt === null) {
            return false;
        }

        return new \DateTime($expiresAt) <= new \DateTime();
    }

    public function getById(int $pollId): array
    {
        if ($pollId <= 0) {
            throw new InvalidArgumentException(
                'O identificador da enquete é inválido.'
            );
        }

        $poll = $this->pollModel->findById($pollId);

        if (!$poll) {
            throw new RuntimeException(
                'Enquete não encontrada.'
            );
        }

        $options = $this->pollModel->findOptionsWithVotes($pollId);

        $totalVotes = array_sum(
            array_map(
                fn (array $option): int =>
                    (int) $option['votes_count'],
                $options
            )
        );

        $formattedOptions = array_map(
            function (array $option) use ($totalVotes): array {
                $votesCount = (int) $option['votes_count'];

                $percentage = $totalVotes > 0
                    ? round(($votesCount / $totalVotes) * 100, 2)
                    : 0;

                return [
                    'id' => (int) $option['id'],
                    'text' => $option['option_text'],
                    'votes_count' => $votesCount,
                    'percentage' => $percentage
                ];
            },
            $options
        );

        return [
            'id' => (int) $poll['id'],
            'title' => $poll['title'],
            'description' => $poll['description'],
            'expires_at' => $poll['expires_at'],
            'created_at' => $poll['created_at'],
            'author' => [
                'id' => (int) $poll['user_id'],
                'name' => $poll['author_name']
            ],
            'total_votes' => $totalVotes,
            'is_expired' => $this->isExpired(
                $poll['expires_at']
            ),
            'options' => $formattedOptions
        ];
    }

    public function update(
        int $pollId,
        int $userId,
        array $data
    ): array {
        if ($pollId <= 0) {
            throw new InvalidArgumentException(
                'O identificador da enquete é inválido.'
            );
        }

        $poll = $this->pollModel->findById($pollId);

        if (!$poll) {
            throw new RuntimeException(
                'Enquete não encontrada.'
            );
        }

        if ((int) $poll['user_id'] !== $userId) {
            throw new RuntimeException(
                'Você não tem permissão para editar esta enquete.'
            );
        }

        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $expiresAt = trim($data['expires_at'] ?? '');

        if ($title === '') {
            throw new InvalidArgumentException(
                'O título da enquete é obrigatório.'
            );
        }

        if ($expiresAt !== '') {
            $expirationDate = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $expiresAt
            );

            if (
                !$expirationDate ||
                $expirationDate->format('Y-m-d H:i:s') !== $expiresAt
            ) {
                throw new InvalidArgumentException(
                    'A data de expiração deve usar o formato Y-m-d H:i:s.'
                );
            }

            if ($expirationDate <= new \DateTime()) {
                throw new InvalidArgumentException(
                    'A data de expiração deve estar no futuro.'
                );
            }
        }

        $this->pollModel->update(
            $pollId,
            $title,
            $description !== '' ? $description : null,
            $expiresAt !== '' ? $expiresAt : null
        );

        return $this->getById($pollId);
    }

    public function delete(
        int $pollId,
        int $userId
    ): void {
        if ($pollId <= 0) {
            throw new InvalidArgumentException(
                'O identificador da enquete é inválido.'
            );
        }

        $poll = $this->pollModel->findById($pollId);

        if (!$poll) {
            throw new RuntimeException(
                'Enquete não encontrada.'
            );
        }

        if ((int) $poll['user_id'] !== $userId) {
            throw new RuntimeException(
                'Você não tem permissão para excluir esta enquete.'
            );
        }

        $this->pollModel->delete($pollId);
    }
}