<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use stdClass;

class JwtService
{
    private string $secret;
    private int $expiration;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? '';
        $this->expiration = (int) ($_ENV['JWT_EXPIRATION'] ?? 3600);

        if ($this->secret === '') {
            throw new RuntimeException(
                'A chave JWT_SECRET não foi configurada.'
            );
        }
    }

    public function generate(array $user): string
    {
        $issuedAt = time();

        $payload = [
            //momento em que o token foi criado
            'iat' => $issuedAt,
            //momento em que o token expira
            'exp' => $issuedAt + $this->expiration,
            //identificador do usuário
            'sub' => (int) $user['id'],
            //informações do usuário
            'user' => [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ];

        return JWT::encode(
            $payload,
            $this->secret,
            'HS256'
        );
    }

    public function decode(string $token): stdClass
    {
        return JWT::decode(
            $token,
            new Key($this->secret, 'HS256')
        );
    }
}