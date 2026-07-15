<?php

namespace App\Middleware;

use App\Services\JwtService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Throwable;

class AuthMiddleware
{
    public function __construct(
        private JwtService $jwtService,
        private ResponseFactoryInterface $responseFactory
    ) {
    }

    public function __invoke(
        Request $request,
        RequestHandler $handler
    ): Response {
        $authorization = $request->getHeaderLine('Authorization');

        if ($authorization === '') {
            return $this->unauthorized(
                'Token de autenticação não informado.'
            );
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return $this->unauthorized(
                'Formato do token inválido.'
            );
        }

        try {
            $payload = $this->jwtService->decode(trim($matches[1]));

            $request = $request->withAttribute(
                'authenticatedUser',
                [
                    'id' => (int) $payload->user->id,
                    'name' => (string) $payload->user->name,
                    'email' => (string) $payload->user->email
                ]
            );

            return $handler->handle($request);
        } catch (Throwable $error) {
            return $this->unauthorized(
                'Token inválido ou expirado.'
            );
        }
    }

    private function unauthorized(string $message): Response
    {
        $response = $this->responseFactory->createResponse(401);

        $json = json_encode(
            [
                'success' => false,
                'message' => $message
            ],
            JSON_UNESCAPED_UNICODE
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        $response->getBody()->write($json);

        return $response->withHeader(
            'Content-Type',
            'application/json; charset=utf-8'
        );
    }
}