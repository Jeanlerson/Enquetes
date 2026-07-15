<?php

use App\Config\Database;
use App\Controllers\UserController;
use App\Models\User;
use App\Services\UserService;

return function ($app) {

    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'message' => 'API funcionando!'
        ]));

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    });

    $app->post('/register', function ($request, $response) {
        try {
            $pdo = Database::getConnection();

            $userModel = new User($pdo);
            $userService = new UserService($userModel);
            $userController = new UserController($userService);

            return $userController->register($request, $response);
        } catch (\Throwable $error) {
            $data = [
                'success' => false,
                'message' => 'Erro interno ao processar o cadastro.',
                'debug' => mb_convert_encoding(
                    $error->getMessage(),
                    'UTF-8',
                    'UTF-8, Windows-1252, ISO-8859-1'
                )
            ];

            $json = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
            );

            // Proteção adicional para nunca enviar false ao write().
            if ($json === false) {
                $json = '{"success":false,"message":"Erro ao gerar resposta JSON."}';
            }

            $response->getBody()->write($json);

            return $response
                ->withHeader(
                    'Content-Type',
                    'application/json; charset=utf-8'
                )
                ->withStatus(500);
        }
    });

    $app->post('/login', function ($request, $response) {
        try {
            $pdo = Database::getConnection();

            $userModel = new User($pdo);
            $userService = new UserService($userModel);
            $userController = new UserController($userService);

            return $userController->login($request, $response);
        } catch (\Throwable $error) {
            $data = [
                'success' => false,
                'message' => 'Erro interno ao processar o login.',
                'debug' => $error->getMessage()
            ];

            $json = json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
            );

            if ($json === false) {
                $json = '{"success":false,"message":"Erro ao gerar resposta JSON."}';
            }

            $response->getBody()->write($json);

            return $response
                ->withHeader(
                    'Content-Type',
                    'application/json; charset=utf-8'
                )
                ->withStatus(500);
        }
    });

    $app->get('/db-test', function ($request, $response) {
        try {
            $pdo = Database::getConnection();
            $result = $pdo->query('SELECT 1 AS test')->fetch();

            $data = [
                'success' => true,
                'message' => 'Conexão com o banco funcionando',
                'result' => $result
            ];

            $status = 200;
        } catch (\Throwable $error) {
            $data = [
                'success' => false,
                'message' => 'Falha na conexão com o banco',
                'error' => $error->getMessage()
            ];

            $status = 500;
        }

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
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    });

    $app->get('/env-test', function ($request, $response) {
        $host = $_ENV['DB_HOST'] ?? '';

        $data = [
            'host' => $host,
            'host_debug' => '[' . $host . ']',
            'host_length' => strlen($host),
            'host_hex' => bin2hex($host),
            'port' => $_ENV['DB_PORT'] ?? null
        ];

        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    });
};