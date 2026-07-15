<?php

return function (
    $app,
    $voteController,
    $authMiddleware
) {
    $app->post(
        '/polls/{id:[0-9]+}/vote',
        [$voteController, 'create']
    )->add($authMiddleware);
};