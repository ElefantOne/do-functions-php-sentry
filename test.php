<?php

require __DIR__ . '/packages/default/sentry/vendor/autoload.php';
require __DIR__ . '/AgentClient.php';

use Sentry\Agent\Transport\AgentClient;

$url = 'https://faas-fra1-afec6ce7.doserverless.co/api/v1/web/fn-e885d530-9581-4dfc-8993-2b82c6a78291/default/sentry';
$dsn = 'telegram://...:...@default?channel=-...';

\Sentry\init([
    'dsn' => 'http://key@example.com/0',
    'send_default_pii' => true,
    'http_client' => new AgentClient($url, $dsn),
]);

throw new Exception('Test exception in the test function');
