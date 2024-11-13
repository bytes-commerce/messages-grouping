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
    protected MessageProcessor $processor;
    private readonly LoggerInterface $logger;

    private readonly \Closure $processingLogic;

    public function __construct(
        MessageBusInterface $messageBus,
        MessageProcessor $processor,
        MessageQueueConfig $config,
        LoggerInterface $logger,
        \Closure $processingLogic = null

    ) {
        $this->processor = $processor;
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

    public function getMessageQueue(): array
    {
        return $this->messageQueue;
    }
}