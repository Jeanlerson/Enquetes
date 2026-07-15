<?php

use App\Config\Database;
use App\Controllers\PollController;
use App\Controllers\UserController;
use App\Controllers\VoteController;
use App\Helpers\JsonResponse;
use App\Middleware\AuthMiddleware;
use App\Models\Poll;
use App\Models\User;
use App\Models\Vote;
use App\Services\JwtService;
use App\Services\PollService;
use App\Services\UserService;
use App\Services\VoteService;

return function ($app) {
    $pdo = Database::getConnection();

    $jwtService = new JwtService();

    $userModel = new User($pdo);
    $userService = new UserService(
        $userModel,
        $jwtService
    );
    $userController = new UserController(
        $userService
    );

    $pollModel = new Poll($pdo);
    $pollService = new PollService(
        $pollModel
    );
    $pollController = new PollController(
        $pollService
    );

    $voteModel = new Vote($pdo);
    $voteService = new VoteService(
        $voteModel,
        $pollModel
    );
    $voteController = new VoteController(
        $voteService
    );

    $authMiddleware = new AuthMiddleware(
        $jwtService,
        $app->getResponseFactory()
    );

    $app->options('/{routes:.+}', function (
        $request,
        $response
    ) {
        return $response;
    });

    $app->get('/health', function (
        $request,
        $response
    ) {
        return JsonResponse::create(
            $response,
            [
                'success' => true,
                'message' => 'API funcionando.'
            ]
        );
    });

    (require __DIR__ . '/auth.php')(
        $app,
        $userController,
        $authMiddleware
    );

    (require __DIR__ . '/polls.php')(
        $app,
        $pollController,
        $authMiddleware
    );

    (require __DIR__ . '/votes.php')(
        $app,
        $voteController,
        $authMiddleware
    );
};