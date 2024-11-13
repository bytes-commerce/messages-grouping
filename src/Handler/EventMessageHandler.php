<?php

namespace App\Handler;

use App\Event\EventMessage;
use Symfony\Component\Messenger\Handler\BatchHandlerInterface;


class EventMessageHandler implements BatchHandlerInterface
{
    public function __invoke(EventMessage $eventMessage)
    {
        echo sprintf("Handling message for Task ID %d: %s", $eventMessage->getTaskId(), $eventMessage->getContent());
    }

    public function flush(bool $force): void
    {
        // TODO: Implement flush() method.
    }
}