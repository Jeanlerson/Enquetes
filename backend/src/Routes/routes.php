<?php
use App\Controllers\UserController;
use App\Services\UserService;
use App\Config\Database;

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

        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    });
};
    

    /*
    $container = $app->getContainer();

    $app->get('/users', function ($request, $response) use ($container) {
        $service = new UserService($container->get('db'));
        $controller = new UserController($service);
        return $controller->index($request, $response);
    });
    */
