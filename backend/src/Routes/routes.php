<?php
use App\Controllers\UserController;
use App\Services\UserService;

return function ($app) {
    $container = $app->getContainer();

    $app->get('/users', function ($request, $response) use ($container) {
        $service = new UserService($container->get('db'));
        $controller = new UserController($service);
        return $controller->index($request, $response);
    });
};