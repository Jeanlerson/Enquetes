<?php

declare(strict_types=1);

use App\Config\Database;
use App\Models\Poll;
use App\Services\PollService;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createMutable(__DIR__ . '/..');
$dotenv->load();

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$frontendUrl = $_ENV['FRONTEND_URL'] ?? 'http://localhost:5173';

header("Access-Control-Allow-Origin: {$frontendUrl}");

header('X-Accel-Buffering: no');

echo "retry: 2000\n\n";

$pollId = filter_input(
    INPUT_GET,
    'poll_id',
    FILTER_VALIDATE_INT
);

if (!$pollId || $pollId <= 0) {
    sendEvent(
        'error',
        [
            'success' => false,
            'message' => 'O identificador da enquete é inválido.'
        ]
    );

    exit;
}

try {
    $pdo = Database::getConnection();

    $pollModel = new Poll($pdo);
    $pollService = new PollService($pollModel);

    $startedAt = time();
    $maximumDuration = 30;

    $lastPayloadHash = null;

    while (
        !connection_aborted()
        && (time() - $startedAt) < $maximumDuration
    ) {
        try {
            $results = $pollService->getResults((int) $pollId);

            $payload = [
                'success' => true,
                'data' => $results
            ];

            $payloadHash = md5(
                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                    | JSON_INVALID_UTF8_SUBSTITUTE
                )
            );

            if ($payloadHash !== $lastPayloadHash) {
                sendEvent('poll-results', $payload);

                $lastPayloadHash = $payloadHash;
            } else {
                echo ": keep-alive\n\n";

                flushOutput();
            }
        } catch (RuntimeException $error) {
            sendEvent(
                'error',
                [
                    'success' => false,
                    'message' => $error->getMessage()
                ]
            );

            break;
        }
        sleep(1);
    }
} catch (Throwable $error) {
    sendEvent(
        'error',
        [
            'success' => false,
            'message' => 'Não foi possível acompanhar os resultados.'
        ]
    );
}

function sendEvent(
    string $eventName,
    array $data
): void {
    $json = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE
    );

    if ($json === false) {
        $json = json_encode([
            'success' => false,
            'message' => 'Erro ao gerar o evento.'
        ]);
    }

    echo "event: {$eventName}\n";
    echo "data: {$json}\n\n";

    flushOutput();
}

function flushOutput(): void
{
    if (ob_get_level() > 0) {
        @ob_flush();
    }

    flush();
}