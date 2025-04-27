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

        if ($position === false) {
            return '?';
        }

        $headerData = substr($data, 0, $position);
        $headers = json_decode($headerData, true);

        if (!is_array($headers)) {
            return '?';
        }

        if (isset($headers['event_id'])) {
            /** @var string $eventID */
            $eventID = $headers['event_id'];

            return $eventID;
        }

        return '?';
    }

    public function sendRequest(Request $request, Options $options): Response
    {
        $data = $request->getStringBody();
        if (empty($data)) {
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
