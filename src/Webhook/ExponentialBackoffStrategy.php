<?php
declare(strict_types=1);

namespace Webhook;

/**
 * Implements an exponential back-off strategy.
 */
class ExponentialBackoffStrategy implements RetryStrategyInterface 
{
    private int $maxDelay;

    /**
     * @param int $maxDelay Maximum delay in seconds.
     */
    public function __construct(int $maxDelay = 60) 
    {
        $this->maxDelay = $maxDelay;
    }

    public function getDelay(int $attempt): int 
    {
        // Delay doubles on each attempt: 1s, 2s, 4s, 8s, ...
        $delay = pow(2, $attempt - 1);

        return min($delay, $this->maxDelay);
    }
}