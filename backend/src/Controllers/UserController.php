<?php
namespace App\Controllers;

use App\Services\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController {
    private UserService $service;

    public function __construct(UserService $service) {
        $this->service = $service;
    }

    public function index(Request $request, Response $response): Response {
        $users = $this->service->getAllUsers();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }
}