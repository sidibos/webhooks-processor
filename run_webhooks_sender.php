<?php
require __DIR__ . '/autoload.php';

use Webhook\EndpointFailureManager;
use Webhook\ExponentialBackoffStrategy;
use Webhook\WebhookLoader;
use Webhook\WebhookSender;
use Webhook\Webhook;

// --- Main Processing Section ---

// Create the retry strategy (exponential back-off with a max delay of 60 seconds).
$retryStrategy = new ExponentialBackoffStrategy(60);

// Create the failure manager with a max of 5 failures per endpoint.
$failureManager = new EndpointFailureManager(5);

// Create the sender with an overall processing time of 80 seconds.
$webhookSender = new WebhookSender($retryStrategy, $failureManager, 80);

// Load webhook events from the CSV file using the generator.
$webhooksFilePath = 'webhooks.txt';
$webhooks = WebhookLoader::loadFromFile($webhooksFilePath);

foreach ($webhooks as $webhook) {
    $webhookSender->processWebhook($webhook);
}

echo "Webhook processing complete.\n";