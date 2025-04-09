<?php

declare(strict_types=1);

namespace Webhook;

/**
 * Sends webhooks using the provided retry strategy.
 */
class WebhookSender 
{
    private int $startTime;
    private bool $maxProcessingTimeReached = false;
    private array $webhooksProcessed = [];

    public function __construct(
        private RetryStrategyInterface $retryStrategy,
        private EndpointFailureManager $failureManager,
        private int $maxProcessingTime = 80
    ) {
        $this->startTime = time();
    }

    /**
     * Sends a list of webhooks with retries and back-off.
     *
     * @param Webhook[] $webhooks
     * 
     * @return void
     */
    public function send(array $webhooks): void
    {
        foreach ($webhooks as $webhook) {
            $endpoint = $webhook->getUrl();

            if ($this->failureManager->shouldSkip($endpoint)) {
                echo "Skipping {$endpoint} due to failure threshold." . PHP_EOL;
                continue;
            }

            $attempt = 1;
            $sent = false;

            while (!$sent) {
                $elapsed = time() - $this->startTime;
                if ($elapsed >= $this->maxProcessingTime) {
                    echo "Max processing time ({$this->maxProcessingTime}s) reached. Stopping further sends." . PHP_EOL;
                    break;
                }

                $sent = $this->sendWebhookRequest($webhook);

                if ($sent) {
                    echo "✅ Webhook sent to {$endpoint} (Order ID: {$webhook->getOrderId()})" . PHP_EOL;
                    $this->processedWebhooks[$endpoint][] = $webhook->getOrderId();
                    break;
                }

                echo "❌ Failed to send webhook to {$endpoint} (Order ID: {$webhook->getOrderId()})" . PHP_EOL;
                $this->failureManager->recordFailure($endpoint);

                if ($this->failureManager->shouldSkip($endpoint)) {
                    echo "Endpoint {$endpoint} has exceeded failure limit. Skipping further attempts." . PHP_EOL;
                    break;
                }

                $delay = $this->retryStrategy->getDelay($attempt);
                if ((time() - $this->startTime) + $delay > $this->maxProcessingTime) {
                    echo "Not enough time to retry {$endpoint}. Skipping." . PHP_EOL;
                    break;
                }

                echo "Retrying in {$delay}s..." . PHP_EOL;
                sleep($delay);
                $attempt++;
            }
        }
    }

    /**
     * List of webhooks processed with succes
     *
     */
    public function getProcessedWebhooks(): array
    {
        return $this->processedWebhooks;
    }

    /**
     * Sends a webhook request.
     *
     * @param Webhook $webhook
     * 
     * @return bool
     */
    private function sendWebhookRequest(Webhook $webhook): bool
    {
        echo "Sending webhook: {$webhook->getUrl()}" . PHP_EOL;

        try {
            $ch = curl_init($webhook->getUrl());
            if (!$ch) {
                throw new RuntimeException("Failed to initialize cURL");
            }

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook->getPayload()));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            if ($response === false) {
                throw new RuntimeException("cURL error: " . curl_error($ch));
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                echo "⚠️  Webhook failed with HTTP code: $httpCode" . PHP_EOL;
                return false;
            }

            return true;
        } catch (\Throwable $th) {
            echo "Error sending webhook to {$webhook->getUrl()}: {$th->getMessage()}" . PHP_EOL;
            return false;
        }
    }
}