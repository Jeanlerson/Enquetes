<?php

namespace App\Services;

use App\Models\User;
use InvalidArgumentException;
use RuntimeException;

class UserService
{
    public function __construct(
        private User $userModel
    ) {
    }

    public function register(array $data): array
    {
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($name === '' || $email === '' || $password === '') {
            throw new InvalidArgumentException(
                'Nome, email e senha são obrigatórios.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'O email informado não é válido.'
            );
        }

        if (strlen($password) < 6) {
            throw new InvalidArgumentException(
                'A senha deve possuir pelo menos 6 caracteres.'
            );
        }

        if ($this->userModel->findByEmail($email)) {
            throw new RuntimeException(
                'Já existe um usuário cadastrado com este email.'
            );
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $userId = $this->userModel->create(
            $name,
            $email,
            $passwordHash
        );

        return [
            'id' => $userId,
            'name' => $name,
            'email' => $email
        ];
    }

    public function login(array $data): array
    {
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            throw new InvalidArgumentException(
                'Email e senha são obrigatórios.'
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'O email informado não é válido.'
            );
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new RuntimeException(
                'Email ou senha incorretos.'
            );
        }

        return [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ];
    }
}