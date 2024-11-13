<?php
namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Service\MessageProcessor;
use App\Config\MessageQueueConfig;
use Psr\Log\LoggerInterface;



class MessageGroupSubscriber implements EventSubscriberInterface
{

    private array $messageQueue = [];
    private MessageBusInterface $messageBus;
    protected MessageProcessor $processor;
    private MessageQueueConfig $config;
    private LoggerInterface $logger;
    private \Closure $processingLogic;

    public function __construct(
        MessageBusInterface $messageBus,
        MessageProcessor $processor,
        MessageQueueConfig $config,
        LoggerInterface $logger,
        \Closure $processingLogic = null

    ) {
        $this->messageBus = $messageBus;
        $this->processor = $processor;
        $this->config = $config;
        $this->logger = $logger;
        $this->processingLogic = $processingLogic ?? $this->getDefaultProcessingLogic();
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
        return function (array $messageQueue) {
            foreach ($messageQueue as $taskId => $messages) {
                try {
                    $this->processor->sendGroupedMessage($taskId, $messages);
                } catch (\Exception $e) {
                    $this->logger->error("Failed to process messages for task {$taskId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        };
    }

    public function getMessageQueue(): array
    {
        return $this->messageQueue;
    }
}