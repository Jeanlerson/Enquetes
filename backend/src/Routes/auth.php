<?php

use App\Helpers\JsonResponse;

return function (
    $app,
    $userController,
    $authMiddleware
) {
    $app->post(
        '/register',
        [$userController, 'register']
    );

    $app->post(
        '/login',
        [$userController, 'login']
    );

    $app->get('/me', function (
        $request,
        $response
    ) {
        $user = $request->getAttribute(
            'authenticatedUser'
        );

        return JsonResponse::create(
            $response,
            [
                'success' => true,
                'user' => $user
            ]
        );
    })->add($authMiddleware);
};