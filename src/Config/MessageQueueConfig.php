<?php

namespace MessagesGrouping\Config;

class MessageQueueConfig
{
    private readonly int $retryLimit;

    private readonly int $groupingInterval;

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
