<?php
declare(strict_types=1);

namespace Webhook;

/**
 * Represents a webhook event.
 */
class Webhook 
{
    private string $url;
    private int $orderId;
    private string $name;
    private string $event;

    /**
     * @param string $url Endpoint URL for the webhook.
     * @param int $orderId Order ID.
     * @param string $name Name.
     * @param string $event Event type.
     */
    public function __construct(string $url, int $orderId, string $name, string $event) 
    {
        // Validate input.
        $this->validateParams($url, $orderId, $name, $event);

        $this->url      = $url;
        $this->orderId  = $orderId;
        $this->name     = $name;
        $this->event    = $event;
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
     */
    private function validateParams(
        string $url, 
        int $orderId, 
        string $name, 
        string $event
    ): void
    {
        // Validate URL.
        if (empty(trim($url)) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL provided: {$url}");
        }

        if($orderId <= 0) {
            throw new \InvalidArgumentException("Invalid order ID provided.");
        }

        if (empty(trim($name))) {
            throw new \InvalidArgumentException("Invalid name provided in payload.");
        }

        if (empty(trim($event))) {
            throw new \InvalidArgumentException("Invalid event provided in payload.");
        }
    }
}