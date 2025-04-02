<?php
namespace Test\Unit;

require_once __DIR__ . '/../../src/Webhook/CSVWebhookLoader.php'; // Include the WebhookSender class.
require_once __DIR__ . '/../../src/Webhook/Webhook.php'; // Include the Webhook class.

use Webhook\CSVWebhookLoader;
use Webhook\Webhook;
use PHPUnit\Framework\TestCase;

/**
 * Test CSVWebhookLoader.
 */
class CSVWebhookLoaderTest extends TestCase {

    private $tempFile;

    protected function setUp(): void {
        // Create a temporary CSV file with a valid header and two rows.
        $this->tempFile = sys_get_temp_dir() . "/test_webhooks.txt";
        $data = <<<CSV
URL,ORDER ID,NAME,EVENT
https://example.com/webhook,123,John Doe,OrderCreated
invalid-url,456,Jane Doe,OrderShipped
CSV;
        file_put_contents($this->tempFile, $data);
    }

    protected function tearDown(): void 
    {
        file_put_contents($this->tempFile, '');
    }

    public function testLoadWebhooks() {
        $loader = new CSVWebhookLoader($this->tempFile);
        $webhooks = $loader->load();
        
        $webhooksAsArray = iterator_to_array($webhooks);
        $count  = count($webhooksAsArray);
        // Only one valid webhook should be loaded.
        $this->assertEquals(1, $count);

        $webhook = $webhooksAsArray[0];
        
        $this->assertInstanceOf(Webhook::class, $webhook);
        
        $this->assertEquals('https://example.com/webhook', $webhook->getUrl());
        $this->assertEquals(123, $webhook->getOrderId());
    }
}