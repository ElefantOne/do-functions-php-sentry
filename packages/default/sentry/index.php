<?php

use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramTransportFactory;
use Symfony\Component\Notifier\Chatter;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Transport\Dsn;

// Error reporting
error_reporting(E_ALL & ~E_DEPRECATED);

// Status codes
const OK = 1;

const ERROR = -1;

/**
 * Just a wrapper function to return the response.
 *
 * @param array $args Arguments to be returned
 *
 * @return array Response with status and result
 */
function wrap(array $args): array
{
    return ['body' => $args];
}

/**
 * Send notification using Telegram.
 *
 * @param array $args Arguments containing the DSN and text
 *
 * @return array Response with status and result
 */
function main(array $args): array
{
    $requiredArgs = [
        'dsn',
        'text',
        'document_data',
    ];

    // Check required arguments
    foreach ($requiredArgs as $arg) {
        if (!isset($args[$arg])) {
            return wrap(['error' => sprintf('Please supply %s argument.', $arg)]);
        }
    }

    // Check arguments
    if (!is_string($args['dsn'])) {
        return wrap(['error' => 'Please supply dsn argument.']);
    }

    if (!is_string($args['text'])) {
        return wrap(['error' => 'Please supply text argument.']);
    }

    if (!is_string($args['document_data'])) {
        return wrap(['error' => 'Please supply document_data argument.']);
    }

    // Send the message
    $result = send($args);

    return wrap(['response' => $result, 'version' => 1]);
}

/**
 * Send the message using Symfony Notifier to Telegram.
 *
 * @param array $args Arguments containing the DSN and text
 *
 * @return array Response with status and result
 */
function send(array $args): array
{
    try {
        /** @var string $dsn */
        $dsn = $args['dsn'];
        /** @var string $text */
        $text = $args['text'];
        /** @var string $documentData */
        $documentData = $args['document_data'];

        $transport = (new TelegramTransportFactory())->create(new Dsn($dsn));
        $chatter = new Chatter($transport);
        $chatMessage = new ChatMessage($text);

        $currentDateTime = new DateTime();
        $formattedDateTime = $currentDateTime->format('Y-m-d_H-i-s');
        $filename = sprintf('error-%s.json', $formattedDateTime);

        // Create a file with the error message
        file_put_contents($filename, $documentData);

        $telegramOptions = (new TelegramOptions())
            ->parseMode('HTML')
            ->disableWebPagePreview(true)
            ->uploadDocument($filename);

        $chatMessage->options($telegramOptions);

        $chatter->send($chatMessage);

        // Remove the file after sending
        if (file_exists($filename)) {
            unlink($filename);
        }
        return ['status' => OK];
    } catch (Throwable $e) {
        return ['status' => ERROR, 'result' => 'Failed to send: ' . $e->getMessage()];
    }
}
