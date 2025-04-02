<?php

declare(strict_types=1);

namespace Webhook;

/**
 * Sends webhooks using the provided retry strategy.
 */
class WebhookSender 
{
    private int $startTime;

    /**
     * @param RetryStrategyInterface $retryStrategy The retry strategy to use.
     * @param EndpointFailureManager $failureManager Manages endpoint failures.
     * @param int $maxProcessingTime Maximum processing time in seconds.
     */
    public function __construct(
        private RetryStrategyInterface $retryStrategy,
        private EndpointFailureManager $failureManager,
        private int $maxProcessingTime = 80
    ) {
        $this->startTime            = time();
    }

    /**
     * Sends a webhook with retries using exponential back-off.
     *
     * @param Webhook $webhook
     * 
     * @return void
     */
    public function send(Webhook $webhook): void
    {
        try {
            $endpoint = $webhook->getUrl();

            // Skip if the endpoint has exceeded the failure limit.
            if ($this->failureManager->shouldSkip($endpoint)) {
                echo "Skip sending webhook for endpoint {$endpoint} due to failure limit reached." . PHP_EOL;
                return;
            }

            $attempt    = 1;
            $sent       = false;
            while (!$sent) {
                // Check if overall processing time is exceeded.
                $elapsed = time() - $this->startTime;
                if ($elapsed >= $this->maxProcessingTime) {
                    echo "Processing time exceeded {$this->maxProcessingTime} seconds. Terminating processing." . PJHP_EOL;
                    exit;
                }

                // Attempt to send the webhook.
                $sent = $this->sendWebhookRequest($webhook);
                if ($sent) {
                    echo "Webhook sent successfully to {$endpoint} for Order ID {$webhook->getOrderId()}." . PHP_EOL;
                } else {
                    echo "Failed to send webhook to {$endpoint} for Order ID {$webhook->getOrderId()}. ";
                    $this->failureManager->recordFailure($endpoint);
                    if ($this->failureManager->shouldSkip($endpoint)) {
                        echo "Exceeded failure limit for {$endpoint}. Aborting further attempts for this endpoint." . PHP_EOL;
                        break;
                    }
                    $delay = $this->retryStrategy->getDelay($attempt);
                    // Check if waiting the delay would exceed max processing time.
                    if ((time() - $this->startTime) + $delay > $this->maxProcessingTime) {
                        echo "Not enough time remaining to retry webhook for {$endpoint}. Skipping this webhook." . PHP_EOL;
                        break;
                    }
                    echo "Retrying in {$delay} seconds..." . PHP_EOL;
                    sleep($delay);
                    $attempt++;
                }
            }
        } catch (Exception $e) {
            echo "Error sending webhook for Order ID {$webhook->getOrderId()}: {$e->getMessage()}" 
            . PHP_EOL;
        }
    }

    /**
     * Send webhook request.
     *
     * @param Webhook $webhook
     * 
     * @return bool True if sending succeeded; false otherwise.
     */
    private function sendWebhookRequest(Webhook $webhook): bool
    {
        echo 'sending webhook: ' . $webhook->getUrl() . PHP_EOL;
        
        try {
            $ch = curl_init($webhook->getUrl());
            if (!$ch) {
                throw new RuntimeException("Failed to initialize cURL");
            }
            
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook->getPayload()));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Prevents long-hanging requests
            
            $response = curl_exec($ch);
            if ($response === false) {
                throw new RuntimeException("cURL error: " . curl_error($ch));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                echo "Webhook failed with HTTP code: $httpCode" . PHP_EOL;
                return false;
            }

            return true;
        } catch (Exception $e) {
            echo "Error sending webhook for URL {$webhook->url}: " . $e->getMessage() ." . PHP_EOL"; 
            return false;
        }
    }
}