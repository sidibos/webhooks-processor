<?php
namespace Test\Unit;

require_once __DIR__ . '/../../src/Webhook/WebhookSender.php'; // Include the WebhookSender class.
require_once __DIR__ . '/../../src/Webhook/Webhook.php'; // Include the Webhook class.

use Webhook\Webhook;
use Webhook\WebhookSender;
use PHPUnit\Framework\TestCase;

class WebhookSenderTest extends TestCase 
{
    public function testSendWebhookSuccess() 
    {
        $webhook = new Webhook("https://example.com", 123, "John Doe", "Spooky Summi");
        $sender = $this->getMockBuilder(WebhookSender::class)
                        ->disableOriginalConstructor()
                       ->onlyMethods(['send'])
                       ->getMock();
        $sender->method('send')->willReturn(true);
        
        $this->assertTrue($sender->send($webhook));
    }
    
    public function testSendWebhookFailure() 
    {
        $webhook = new Webhook("https://example.com", 123, "John Doe", "Spooky Summi");
        $sender = $this->getMockBuilder(WebhookSender::class)
                       ->onlyMethods(['send'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $sender->method('send')->willReturn(false);
        
        $this->assertFalse($sender->send($webhook));
    }
}