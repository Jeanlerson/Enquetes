<?php

namespace App\Services;

use App\Models\Poll;
use DateTime;
use InvalidArgumentException;

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
}