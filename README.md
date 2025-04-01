# Webhooks Sender/Processor

This project provides an object‑oriented PHP solution for processing and sending webhook events read from a text file in CSV format. The solution leverages several design patterns to provide modular, maintainable, and efficient processing. It includes features like validation of webhook parameters, exponential back‑off retry logic, and endpoint failure management.

---

## Overview

- **Text File Format:**  
  The webhook file (`webhooks.txt`) should be a comma-separated values (CSV) text file with a header row. The expected header columns are:
  - `URL`
  - `ORDER ID`
  - `NAME`
  - `EVENT`

- **Validation:**  
  The application validates each webhook parameter:
  - **URL:** Must be a valid URL string.
  - **Order ID:** Must be an integer greater than zero.
  - **Name:** Must not be empty.
  - **Event:** Must not be empty.

- **Design Patterns Used:**
  - **Generator Pattern:**  
    Uses a generator in the `CSVWebhookLoader` to lazily read and yield webhook objects from the CSV file.
  - **Strategy Pattern:**  
    Implements an exponential back‑off strategy in `ExponentialBackoffStrategy` to calculate retry delays.
  - **Facade/Service Pattern:**  
    The `WebhookSender` class serves as a facade to coordinate sending of webhooks, handling retries, and managing endpoint failure counts.
  - **Dependency Injection:**  
    Dependencies such as the retry strategy and endpoint failure manager are injected into the sender, promoting decoupling and easier testing.

- **Retry Logic:**  
  When a webhook send fails, the solution uses exponential back‑off (starting at 1 second and doubling up to a maximum of 60 seconds) and stops trying if:
  - An endpoint fails 5 times.
  - The overall processing time exceeds 80 seconds.

---

## Requirements

- PHP 7 or later.
- A text file named `webhooks.txt` in the project directory with the correct format.

---

## Files

- **webhooks.txt:**  
  The input text file containing webhook events.

- **WebhookSender Code:**  
  The main PHP script includes the following classes:
  - `Webhook` – Represents a webhook event and validates its parameters.
  - `WebhookLoader` – Loads webhook events from the text file using a generator.
  - `ExponentialBackoffStrategy` – Implements the exponential back‑off delay strategy.
  - `EndpointFailureManager` – Tracks and manages failure counts per endpoint.
  - `WebhookSender` – Coordinates sending webhooks, applying retry logic and handling failures.

---

## How to Run

1. **Prepare the text file in CSV format:**  
   Create a file named `webhooks.txt` in the project directory with the following format:

   ```bash
   URL,ORDER ID,NAME,EVENT
   https://example.com/webhook,123,John Doe,Test Event
   https://example.com/webhook,124,Jane Smith,Test Event two
   ```

2. **Configure Your Environment:**  
   Ensure that you have PHP installed and available in your system path.

3. **Run the Script:**  
   From the command line, execute the PHP script:
   ```bash
   php run_webhook_sender.php
   ```
   The script will process the CSV file, validate each webhook event, and attempt to send each webhook using the specified retry logic.

---

### How to Run the Tests

1. **Install PHPUnit:**  
   If you haven’t installed PHPUnit globally, you can install it via Composer:
   ```bash
   composer require --dev phpunit/phpunit
   ```

2. **Run the Test Suite:**  
   Navigate to your project root directory and run:
   Execute PHPUnit in your project directory:
   ```bash
   vendor/bin/phpunit tests
   ```
   This will run all the tests defined in the tests directory.

---

## Customization

- **Configuration Parameters:**  
  You can adjust the maximum processing time, maximum delay for retries, and the allowed number of failures per endpoint by modifying the constructor parameters of `WebhookSender`, `ExponentialBackoffStrategy`, and `EndpointFailureManager`.

- **Logging and Error Handling:**  
  Additional logging or more sophisticated error handling can be implemented as needed based on the project requirements.

---
