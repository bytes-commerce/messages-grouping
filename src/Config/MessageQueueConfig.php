<?php

namespace App\Config;

class MessageQueueConfig
{
    private int $retryLimit;
    private int $groupingInterval;

    public function __construct(int $retryLimit = 3, int $groupingInterval = 300)
    {
        $this->retryLimit = $retryLimit;
        $this->groupingInterval = $groupingInterval;
    }

    public function getRetryLimit(): int
    {
        return $this->retryLimit;
    }

    public function getGroupingInterval(): int
    {
        return $this->groupingInterval;
    }
}
