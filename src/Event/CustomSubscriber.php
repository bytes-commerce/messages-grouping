<?php
namespace App\Event;

use App\Config\MessageQueueConfig;
use App\Service\MessageProcessor;
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

    protected function createProcessingLogic(): callable
    {
        return function (array $messages): void {
            foreach ($messages as $taskId => $messageGroup) {
                $this->processor->sendGroupedMessage($taskId, $messageGroup);
            }
        };
    }
}