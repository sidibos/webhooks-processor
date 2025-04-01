<?php
namespace Tests\Unit;

require_once __DIR__ . '/../../src/Webhook/Webhook.php'; // Include the Webhook class.

use Webhook\Webhook;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase 
{
    protected string $eventName;

    public function tearUp(): void
    {
        $this->eventName = "Test Event";
    }

    public function testValidWebhookCreation() 
    {
        $webhook = new Webhook("https://example.com", 1, "John Doe", "Test Event");
        $payload = $webhook->getPayload();
        $this->assertEquals("https://example.com", $webhook->getUrl());
        $this->assertEquals(1, $webhook->getOrderId());
        $this->assertEquals("John Doe", $payload['name']);
        $this->assertEquals("Test Event", $payload['event']);
    }

    public function testInvalidUrl() 
    {
        $this->expectException(\InvalidArgumentException::class);
        new Webhook("", 1, "John Doe", "Test Event");
    }

    public function testInvalidOrderId() 
    {
        $this->expectException(\InvalidArgumentException::class);
        new Webhook("https://example.com", 0, "John Doe", "Test Event");
    }

    public function testInvalidCustomerName() 
    {
        $this->expectException(\InvalidArgumentException::class);
        new Webhook("https://example.com", 1, "John123", "");
    }
}