<?php

declare(strict_types=1);

namespace Sentry\Agent\Transport;

use Sentry\HttpClient\HttpClientInterface;
use Sentry\HttpClient\Request;
use Sentry\HttpClient\Response;
use Sentry\Options;

class AgentClientTest implements HttpClientInterface
{
    public function getEventID(string $data): string
    {
        $position = strpos($data, "\n");

        if (!$position) {
            return '?';
        }

        $headerData = substr($data, 0, $position);
        /** @var array $decoded */
        $decoded = json_decode($headerData, true);

        if (isset($decoded['event_id']) && is_string($decoded['event_id'])) {
            /** @var string $data */
            $data = $decoded['event_id'];
            return $data;
        }

        return '?';
    }

    #[\Override]
    public function sendRequest(Request $request, Options $options): Response
    {
        $data = (string) $request->getStringBody();
        if ($data === '') {
            return new Response(400, [], 'Request body is empty');
        }

        $eventID = $this->getEventID($data);

        $args = [
            // check chat id: https://api.telegram.org/bot{token}/getUpdates
            'dsn' => 'telegram://...:...@default?channel=-...',
            'text' => sprintf('Event ID: %s', $eventID),
            'document_data' => $data,
        ];

        $response = main($args);
        echo "Response:\n";
        print_r($response);

        // Since we are sending async there is no feedback so we always return an empty response
        return new Response(202, [], '');
    }
}
