<?php
namespace Webhook;

/**
 * Interface for a retry strategy.
 */
interface RetryStrategyInterface {
    /**
     * Returns the delay (in seconds) for the given attempt.
     *
     * @param int $attempt The attempt count (
     * 1 for first retry, 
     * 2 for second, etc.
     * )
     * 
     * @return int Delay in seconds.
     */
    public function getDelay(int $attempt): int;
}
