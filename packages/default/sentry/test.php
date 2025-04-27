<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/index.php';
require __DIR__ . '/AgentClientTest.php';

use Sentry\Agent\Transport\AgentClientTest;

\Sentry\init([
    // https://examplePublicKey@o0.ingest.sentry.io/0
    'dsn' => 'http://key@example.com/0',
    'send_default_pii' => true,
    'http_client' => new AgentClientTest(),
]);

try {
    throw new Exception('Test exception');
} catch (Throwable $exception) {
    \Sentry\captureException($exception);
}
