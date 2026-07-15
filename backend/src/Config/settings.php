<?php
return function ($app) {
    $container = $app->getContainer();

    $container->set('db', function () {
        $pdo = new PDO("mysql:host=localhost;dbname=slim_app", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    });
};
