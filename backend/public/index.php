<?php
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createMutable(__DIR__ . '/..');
$dotenv->safeLoad();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$frontendUrl = $_ENV['FRONTEND_URL']
    ?? 'http://localhost:5173';

$app->add(function ($request, $handler) use ($frontendUrl) {
    $response = $handler->handle($request);

    return $response
        ->withHeader(
            'Access-Control-Allow-Origin',
            $frontendUrl
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

(require __DIR__ . '/../src/Routes/routes.php')($app);

$app->run();
