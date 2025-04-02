<?php
declare(strict_types=1);

namespace Webhook;

use Exception;

/**
 * Loads webhooks from a CSV file.
 * Expects a CSV with a header row: URL, ORDER ID, NAME, EVENT.
 */
class CSVWebhookLoader 
{
    private const EXPECTED_HEADER = ['URL', 'ORDER ID', 'NAME', 'EVENT'];

    /**
     * @param string $filePath Path to the CSV file.
     */
    public function __construct(
        private string $filePath,
        private array $expectedHeader = self::EXPECTED_HEADER
    ) 
    {
        if (empty($this->filePath)) {
            throw new InvalidArgumentException("File path cannot be empty");
        }
    }

    /**
     * Loads webhook objects from a CSV file.
     *
     * @return \Generator Yields valid Webhook objects.
     * 
     * @throws Exception if the file cannot be opened or the header is invalid.
     */
    public function load(): \Generator 
    {
        if (!file_exists($this->filePath)) {
            throw new Exception("File not found: {$this->filePath}");
        }
        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new Exception("Unable to open file: {$this->filePath}");
        }
        
        // Read header row.
        $headers = fgetcsv($handle);
        $headers = \array_map('trim', $headers);
        $headers = \array_map('strtoupper', $headers);
        
        if (
            $headers === false 
            || count($headers) != count($this->expectedHeader)
            || array_diff($headers, $this->expectedHeader) != array_diff($this->expectedHeader, $headers)
        ) {
            fclose($handle);
            throw new Exception("Invalid headers in {$this->filePath}");
        }

        $headersSize = count($headers);

        // Loop through each row.
        while (($row = fgetcsv($handle)) !== false) {
            // Expecting 4 columns: URL, ORDER ID, NAME, EVENT.
            if (count($row) < $headersSize) {
                continue; // Skip incomplete rows.
            }
            list($url, $orderId, $name, $event) = $row;
            try {
                /**
                 * We are using an object instead of an array to represent the webhook.
                 *  This is because the Webhook class enforces type safety and validation.
                 *  which might increase the reliability of the webhook processing.
                 *  We could use an array here, but it would be less type-safe.
                 *  might increase the performance of the webhook processing.
                 */
                yield new Webhook(trim($url), (int) trim($orderId), trim($name), trim($event));
            } catch (InvalidArgumentException $e) {
                echo "Skipping invalid webhook: " . $e->getMessage() . PHP_EOL;
            }
        }
        fclose($handle);
    }
}