<?php
require __DIR__ . '/autoload.php';

use Webhook\Webhook;
use Webhook\WebhookSender;
use Webhook\CSVWebhookLoader;
use Webhook\EndpointFailureManager;
use Webhook\ExponentialBackoffStrategy;
use Webhook\WebhookLoader;

// --- Main Processing Section ---

try {
    // Create the retry strategy (exponential back-off with a max delay of 60 seconds).
    $retryStrategy = new ExponentialBackoffStrategy(60);

    // Create the failure manager with a max of 5 failures per endpoint.
    $failureManager = new EndpointFailureManager(5);

    // Create the sender with an overall processing time of 80 seconds.
    $webhookSender = new WebhookSender($retryStrategy, $failureManager, 80);

    // Load webhook events from the CSV file using the generator.
    $webhooksFilePath = 'webhooks.txt';
    $webhooks = (new CSVWebhookLoader($webhooksFilePath))->load();

    if (empty($webhooks)) {
        echo "No webhooks to process." . PHP_EOL;
        exit(0);
    }

    $webhookSender->send($webhooks);

    echo '<pre>';
    print_r($webhookSender->getProcessedWebhooks());
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}

echo "Webhook processing complete." . PHP_EOL;