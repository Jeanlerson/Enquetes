<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Carregar configurações
(require __DIR__ . '/../src/Config/settings.php')($app);

// Carregar middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Carregar rotas
(require __DIR__ . '/../src/Routes/routes.php')($app);

$app->run();
