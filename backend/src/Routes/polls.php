<?php

return function (
    $app,
    $pollController,
    $authMiddleware
) {
    $app->get(
        '/polls',
        [$pollController, 'index']
    );

    $app->get(
        '/polls/{id:[0-9]+}',
        [$pollController, 'show']
    );

    $app->get(
        '/polls/{id:[0-9]+}/results',
        [$pollController, 'results']
    );

    $app->post(
        '/polls',
        [$pollController, 'create']
    )->add($authMiddleware);

    $app->put(
        '/polls/{id:[0-9]+}',
        [$pollController, 'update']
    )->add($authMiddleware);

    $app->delete(
        '/polls/{id:[0-9]+}',
        [$pollController, 'delete']
    )->add($authMiddleware);
};