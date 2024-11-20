<?php

namespace MessagesGrouping\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use MessagesGrouping\Service\MessageProcessor;
use MessagesGrouping\Config\MessageQueueConfig;
use Psr\Log\LoggerInterface;

class MessageGroupSubscriber implements EventSubscriberInterface
{

    /**
     * @var array<int|string, EventMessage[]>
     */
    private array $messageQueue = [];

    protected MessageProcessor $processor;

    private readonly LoggerInterface $logger;

    private readonly \Closure $processingLogic;

    private readonly MessageQueueConfig $config;

    private readonly MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus,
        private readonly MessageProcessor $processor,
        MessageQueueConfig $config,
        LoggerInterface $logger,
        \Closure|null $processingLogic = null
    ) {
        $this->processor = $processor;
        $this->logger = $logger;
        $this->processingLogic = $processingLogic ?? $this->getDefaultProcessingLogic();
        $this->config = $config;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'event.messsage_dispatch' => 'onMessageDispatch'
        ];
    }

    public function onMessageDispatch(EventMessage $eventMessage): void
    {
        $taskId = $eventMessage->getTaskId();
        $this->messageQueue[$taskId][] = $eventMessage;
    }

    public function processGroupedMessages(): void
    {
        ($this->processingLogic)($this->messageQueue);
        $this->messageQueue = [];
    }

    private function getDefaultProcessingLogic(): \Closure
    {
        return function (array $messageQueue): void {
            foreach ($messageQueue as $taskId => $messages) {
                try {
                    $this->processor->sendGroupedMessage($taskId, $messages);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to process messages for task ' . $taskId, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        };
    }

    /**
     * @return array<int|string, EventMessage[]>
     */
    public function getMessageQueue(): array
    {
        return $this->messageQueue;
    }

    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }

    public function getMsgConfig(): MessageQueueConfig
    {
        return $this->config;
    }
}
