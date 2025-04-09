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
     * @return array Returns an array of valid Webhook objects.
     * 
     * @throws Exception if the file cannot be opened or the header is invalid.
     */
    public function load(): array
    {
        if (!file_exists($this->filePath)) {
            throw new Exception("File not found: {$this->filePath}");
        }

        $handle = fopen($this->filePath, 'r');
        if ($handle === false) {
            throw new Exception("Unable to open file: {$this->filePath}");
        }
        
        // Read header row and normalize it.
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

        $webhooks = []; // List to collect Webhook objects
        $headersSize = count($headers);

        // Loop through each row.
        while (($row = fgetcsv($handle)) !== false) {
            // Skip rows with missing data
            if (count($row) < $headersSize) {
                continue;
            }

            list($url, $orderId, $name, $event) = $row;

            try {
                // Create a Webhook object and add it to the array
                $webhooks[] = new Webhook(
                    trim($url),
                    (int) trim($orderId),
                    trim($name),
                    trim($event)
                );
            } catch (\Throwable $th) {
                echo "Skipping invalid webhook: " . $th->getMessage() . PHP_EOL;
            }
        }

        fclose($handle);

        return $webhooks;
    }
}