<?php

namespace App\Controllers;

use App\Services\VoteService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Throwable;

class VoteController
{
    public function __construct(
        private VoteService $service
    ) {
    }

    public function create(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $pollId = (int) ($args['id'] ?? 0);

            $authenticatedUser = $request->getAttribute(
                'authenticatedUser'
            );

            $data = (array) $request->getParsedBody();

            $vote = $this->service->vote(
                $pollId,
                (int) $authenticatedUser['id'],
                $data
            );

            return $this->jsonResponse(
                $response,
                [
                    'success' => true,
                    'message' => 'Voto registrado com sucesso.',
                    'vote' => $vote
                ],
                201
            );
        } catch (InvalidArgumentException $error) {
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                422
            );
        } catch (RuntimeException $error) {
            $status = match ($error->getMessage()) {
                'Enquete não encontrada.' => 404,
                'Você já votou nesta enquete.' => 409,
                'Esta enquete já expirou.' => 409,
                default => 400
            };

            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                $status
            );
        } catch (Throwable $error) {
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível registrar o voto.',
                    'debug' => $error->getMessage()
                ],
                500
            );
        }
    }

    private function jsonResponse(
        Response $response,
        array $data,
        int $status
    ): Response {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            $json = '{"success":false,"message":"Erro ao gerar JSON."}';
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