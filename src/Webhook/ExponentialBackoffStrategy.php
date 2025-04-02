<?php
declare(strict_types=1);

namespace Webhook;

/**
 * Implements an exponential back-off strategy.
 */
class ExponentialBackoffStrategy implements RetryStrategyInterface
{
    /**
     * @param int $maxDelay Maximum delay in seconds.
     */
    public function __construct(private int $maxDelay = 60)
    {
    }

    /**
     * Returns the delay (in seconds) for the given attempt.
     * 
     * @param int $attempt The attempt count
     * 
     * @return int Delay in seconds.
     */
    public function getDelay(int $attempt): int
    {
        // Delay doubles on each attempt: 1s, 2s, 4s, 8s, ...
        $delay = pow(2, $attempt - 1);

        return min($delay, $this->maxDelay);
    }
}