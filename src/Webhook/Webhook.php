<?php
declare(strict_types=1);

namespace Webhook;

/**
 * Represents a webhook event.
 */
class Webhook 
{
    /**
     * @param string $url Endpoint URL for the webhook.
     * @param int $orderId Order ID.
     * @param string $name Name.
     * @param string $event Event type.
     */
    public function __construct(
        private string $url,
        private int $orderId,
        private string $name,
        private string $event
    )
    {
        // Validate input.
        $this->validateParams();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Get the payload for the webhook.
     * 
     * @return array
     */
    public function getPayload(): array 
    {
        return [
            'order_id'  => $this->orderId,
            'name'      => $this->name,
            'event'     => $this->event
        ];
    }

    /**
     * Validate the input parameters.
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException If any parameter is invalid.
     */
    private function validateParams(): void
    {
        // Validate URL.
        if (empty(trim($this->url)) || !filter_var($this->url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL provided: {$url}");
        }

        if($this->orderId <= 0) {
            throw new \InvalidArgumentException("Invalid order ID provided.");
        }

        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException("Invalid name provided in payload.");
        }

        if (empty(trim($this->event))) {
            throw new \InvalidArgumentException("Invalid event provided in payload.");
        }
    }
}