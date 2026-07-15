<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;

class JsonResponse
{
    public static function create(
        Response $response,
        array $data,
        int $status = 200
    ): Response {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            $json = '{"success":false,"message":"Erro ao gerar resposta JSON."}';
            $status = 500;
        }

        $response->getBody()->write($json);

        return $response
            ->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            )
            ->withStatus($status);
    }
}