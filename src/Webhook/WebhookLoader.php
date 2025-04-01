<?php
declare(strict_types=1);

namespace Webhook;

use Exception;

class WebhookLoader 
{
    public static function loadFromFile(string $filePath): \Generator 
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new Exception("Unable to open file: {$filePath}");
        }
        
        // Read header row.
        $headers = fgetcsv($handle);
        if ($headers === false || count($headers) < 4) {
            fclose($handle);
            throw new Exception("Invalid header in {$filePath}");
        }

        // Loop through each row.
        while (($row = fgetcsv($handle)) !== false) {
            // Expecting 4 columns: URL, ORDER ID, NAME, EVENT.
            if (count($row) < 4) {
                continue; // Skip incomplete rows.
            }
            list($url, $orderId, $name, $event) = $row;
            yield new Webhook(trim($url), (int) trim($orderId), trim($name), trim($event));
        }
        fclose($handle);
    }
}