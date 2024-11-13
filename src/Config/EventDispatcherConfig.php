<?php

namespace App\Config;

class EventDispatcherConfig
{
    private int $maxGroupedEvents;

    public function __construct(int $maxGroupedEvents = 10)
    {
        $this->maxGroupedEvents = $maxGroupedEvents;
    }

    public function getMaxGroupedEvents(): int
    {
        return $this->maxGroupedEvents;
    }
}