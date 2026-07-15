<?php
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv::createMutable(__DIR__ . '/..');
$dotenv->load();

// Criar aplicação Slim
$app = AppFactory::create();

// Carregar middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Carregar configurações
//(require __DIR__ . '/../src/Config/settings.php')($app);
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader(
            'Access-Control-Allow-Origin',
            'http://localhost:5173'
        )
        ->withHeader(
            'Access-Control-Allow-Headers',
            'Content-Type, Authorization'
        )
        ->withHeader(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, DELETE, OPTIONS'
        );
});

// Carregar rotas
(require __DIR__ . '/../src/Routes/routes.php')($app);

$app->run();
