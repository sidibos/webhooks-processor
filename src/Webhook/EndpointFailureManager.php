<?php
declare(strict_types=1);

namespace Webhook;

/**
 * Manages endpoint failure counts.
 */
class EndpointFailureManager
{
    private array $failureCounts = [];

    /**
     * @param int $maxFailures Maximum allowed failures before skipping an endpoint.
     */
    public function __construct(private int $maxFailures = 5)
    {
    }

    /**
     * Increment the failure count for an endpoint.
     *
     * @param string $endpoint
     * 
     * @return void
     */
    public function recordFailure(string $endpoint): void
    {
        if (!isset($this->failureCounts[$endpoint])) {
            $this->failureCounts[$endpoint] = 0;
        }
        $this->failureCounts[$endpoint]++;
    }

    /**
     * Check if an endpoint has exceeded the maximum failures.
     *
     * @param string $endpoint
     * 
     * @return bool
     */
    public function shouldSkip(string $endpoint): bool
    {
        return isset($this->failureCounts[$endpoint])
            && $this->failureCounts[$endpoint] >= $this->maxFailures;
    }
}