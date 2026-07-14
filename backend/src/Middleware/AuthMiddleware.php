<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware {
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        $token = $request->getHeaderLine('Authorization');
        if ($token !== 'Bearer meu_token_secreto') {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        return $next($request, $response);
    }
}