<?php
namespace Test\Unit;

require_once __DIR__ . '/../../src/Webhook/WebhookLoader.php'; // Include the WebhookSender class.
require_once __DIR__ . '/../../src/Webhook/Webhook.php'; // Include the Webhook class.

use Webhook\Webhook;
use Webhook\WebhookLoader;
use PHPUnit\Framework\TestCase;

class WebhookLoaderTest extends TestCase 
{
    public function testLoadFromFile() 
    {
        $filePath = sys_get_temp_dir() . "/test_webhooks.txt";
        file_put_contents(
            $filePath, 
            "URL,ORDER ID,Name,Event\nhttps://example.com,1,John Doe,Test Event\n"
        );
        
        $webhooks = WebhookLoader::loadFromFile($filePath);
        
        $webhooksAsArray = iterator_to_array($webhooks);
        $count  = count($webhooksAsArray);
        $this->assertEquals(1, $count);
        
        $this->assertInstanceOf(Webhook::class, $webhooksAsArray[0]);
        file_put_contents($filePath, '');
    }
}
