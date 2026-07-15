<?php

namespace App\Controllers;

use App\Services\UserService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Throwable;
use App\Helpers\JsonResponse;

class UserController
{
    public function __construct(
        private UserService $service
    ) {
    }

    public function register(
        Request $request,
        Response $response
    ): Response {
        try {
            $data = (array) $request->getParsedBody();
            $user = $this->service->register($data);

            return $this->jsonResponse(
                $response,
                [
                    'success' => true,
                    'message' => 'Usuário cadastrado com sucesso.',
                    'user' => $user
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
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                409
            );
        } catch (Throwable $error) {
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível cadastrar o usuário.',
                ],
                500
            );
        }
    }

    public function login(
        Request $request,
        Response $response
    ): Response {
        try {
            $data = (array) $request->getParsedBody();
            $result = $this->service->login($data);

            return $this->jsonResponse(
                $response,
                [
                    'success' => true,
                    'message' => 'Login realizado com sucesso.',
                    'user' => $result['user'],
                    'token' => $result['token']
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
                401
            );
        } catch (Throwable $error) {
            return $this->jsonResponse(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível realizar o login.',
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
            $json = json_encode([
                'success' => false,
                'message' => 'Erro ao gerar resposta JSON.'
            ]);
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