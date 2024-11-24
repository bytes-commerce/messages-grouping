<?php
namespace MessagesGrouping\Event;

use Closure;
use MessagesGrouping\Config\MessageQueueConfig;
use MessagesGrouping\Service\MessageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CustomSubscriber extends MessageGroupSubscriber
{
    public function __construct(
        MessageBusInterface $messageBus,
        MessageProcessor $processor,
        MessageQueueConfig $config,
        LoggerInterface $logger
    ) {
        parent::__construct($messageBus, $processor, $config, $logger, $this->createProcessingLogic());
    }

    protected function createProcessingLogic(): Closure|null
    {
        return function (array $messages): void {
            foreach ($messages as $taskId => $messageGroup) {
                $this->processor->sendGroupedMessage($taskId, $messageGroup);
            }
        };
    }
}