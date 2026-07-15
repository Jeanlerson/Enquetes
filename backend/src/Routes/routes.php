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
    /*
     * Dependências compartilhadas
     */
    $pdo = Database::getConnection();

    $jwtService = new JwtService();

    /*
     * Usuários e autenticação
     */
    $userModel = new User($pdo);

    $userService = new UserService(
        $userModel,
        $jwtService
    );

    $userController = new UserController(
        $userService
    );

    /*
     * Enquetes
     */
    $pollModel = new Poll($pdo);

    $pollService = new PollService(
        $pollModel
    );

    $pollController = new PollController(
        $pollService
    );

    /*
     * Votos
     */
    $voteModel = new Vote($pdo);

    $voteService = new VoteService(
        $voteModel,
        $pollModel
    );

    $voteController = new VoteController(
        $voteService
    );

    /*
     * Middleware
     */
    $authMiddleware = new AuthMiddleware(
        $jwtService,
        $app->getResponseFactory()
    );

    /*
     * Saúde da API
     */
    $app->get('/health', function (
        $request,
        $response
    ) {
        return JsonResponse::create(
            $response,
            [
                'success' => true,
                'message' => 'API funcionando.'
            ],
            200
        );
    });

    /*
     * Autenticação
     */
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
            ],
            200
        );
    })->add($authMiddleware);

    /*
     * Enquetes públicas
     */
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

    /*
     * Enquetes protegidas
     */
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

    /*
     * Votação
     */
    $app->post(
        '/polls/{id:[0-9]+}/vote',
        [$voteController, 'create']
    )->add($authMiddleware);
};