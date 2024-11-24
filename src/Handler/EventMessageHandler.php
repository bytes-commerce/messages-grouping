<?php

namespace MessagesGrouping\Handler;

use MessagesGrouping\Event\EventMessage;
use Symfony\Component\Messenger\Handler\BatchHandlerInterface;


class EventMessageHandler implements BatchHandlerInterface
{
    public function __invoke(EventMessage $eventMessage): void
    {
        echo sprintf("Handling message for Task ID %d: %s.\n ", $eventMessage->getTaskId(), $eventMessage->getContent());
    }

    public function flush(bool $force): void
    {
    }
}