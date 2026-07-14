<?php
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Criar aplicação Slim
$app = AppFactory::create();

// Carregar middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Carregar configurações
//(require __DIR__ . '/../src/Config/settings.php')($app);

// Carregar rotas
(require __DIR__ . '/../src/Routes/routes.php')($app);

$app->run();
