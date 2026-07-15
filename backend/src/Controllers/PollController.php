<?php

namespace App\Controllers;

use App\Helpers\JsonResponse;
use App\Services\PollService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Throwable;

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

            return JsonResponse::create(
                $response,
                [
                    'success' => true,
                    'message' => 'Enquete criada com sucesso.',
                    'poll' => $poll
                ],
                201
            );
        } catch (InvalidArgumentException $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                422
            );
        } catch (Throwable $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível criar a enquete.'
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

            return JsonResponse::create(
                $response,
                [
                    'success' => true,
                    'polls' => $polls
                ],
                200
            );
        } catch (Throwable $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível listar as enquetes.'
                ],
                500
            );
        }
    }

    public function show(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $pollId = (int) ($args['id'] ?? 0);

            $poll = $this->service->getById($pollId);

            return JsonResponse::create(
                $response,
                [
                    'success' => true,
                    'poll' => $poll
                ],
                200
            );
        } catch (InvalidArgumentException $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                422
            );
        } catch (RuntimeException $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                404
            );
        } catch (Throwable $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível carregar a enquete.'
                ],
                500
            );
        }
    }

    public function update(
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

            $poll = $this->service->update(
                $pollId,
                (int) $authenticatedUser['id'],
                $data
            );

            return JsonResponse::create(
                $response,
                [
                    'success' => true,
                    'message' => 'Enquete atualizada com sucesso.',
                    'poll' => $poll
                ],
                200
            );
        } catch (InvalidArgumentException $error) {
            return JsonResponse::create(
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
                'Você não tem permissão para editar esta enquete.' => 403,
                default => 400
            };

            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                $status
            );
        } catch (Throwable $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível atualizar a enquete.'
                ],
                500
            );
        }
    }

    public function delete(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $pollId = (int) ($args['id'] ?? 0);

            $authenticatedUser = $request->getAttribute(
                'authenticatedUser'
            );

            $this->service->delete(
                $pollId,
                (int) $authenticatedUser['id']
            );

            return JsonResponse::create(
                $response,
                [
                    'success' => true,
                    'message' => 'Enquete excluída com sucesso.'
                ],
                200
            );
        } catch (InvalidArgumentException $error) {
            return JsonResponse::create(
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
                'Você não tem permissão para excluir esta enquete.' => 403,
                default => 400
            };

            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                $status
            );
        } catch (Throwable $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível excluir a enquete.'
                ],
                500
            );
        }
    }

    public function results(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $pollId = (int) ($args['id'] ?? 0);

            $results = $this->service->getResults($pollId);

            return JsonResponse::create(
                $response,
                [
                    'success' => true,
                    'data' => $results
                ],
                200
            );
        } catch (InvalidArgumentException $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                422
            );
        } catch (RuntimeException $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ],
                404
            );
        } catch (Throwable $error) {
            return JsonResponse::create(
                $response,
                [
                    'success' => false,
                    'message' => 'Não foi possível carregar os resultados.'
                ],
                500
            );
        }
    }
}