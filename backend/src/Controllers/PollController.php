<?php

namespace App\Controllers;

use App\Services\PollService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;
use RuntimeException;

class PollController
{
    public function __construct(
        private PollService $service
    ) {
    }

    public function create(
        Request $request,
        Response $response
    ): Response {
        try {
            $authenticatedUser = $request->getAttribute(
                'authenticatedUser'
            );

            $data = (array) $request->getParsedBody();

            $poll = $this->service->create(
                $data,
                (int) $authenticatedUser['id']
            );

            return $this->jsonResponse(
                $response,
                [
                    'success' => true,
                    'message' => 'Enquete criada com sucesso.',
                    'poll' => $poll
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
        } catch (Throwable $error) {
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível criar a enquete.',
                    'debug' => $error->getMessage()
                ],
                500
            );
        }
    }

    public function index(
        Request $request,
        Response $response
    ): Response {
        try {
            $polls = $this->service->getAll();

            return $this->jsonResponse(
                $response,
                [
                    'success' => true,
                    'polls' => $polls
                ],
                200
            );
        } catch (Throwable $error) {
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível listar as enquetes.',
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

    public function show(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $pollId = (int) ($args['id'] ?? 0);

            $poll = $this->service->getById($pollId);

            return $this->jsonResponse(
                $response,
                [
                    'success' => true,
                    'poll' => $poll
                ],
                200
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
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                404
            );
        } catch (Throwable $error) {
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível carregar a enquete.',
                    'debug' => $error->getMessage()
                ],
                500
            );
        }
    }
}