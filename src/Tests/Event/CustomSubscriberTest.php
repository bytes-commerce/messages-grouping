<?php

namespace App\Tests\Event;

use App\Config\MessageQueueConfig;
use App\Event\CustomSubscriber;
use App\Event\EventMessage;
use App\Service\MessageProcessor;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CustomSubscriberTest extends TestCase
{
    private CustomSubscriber $customSubscriber;
    private $messageBusMock;
    private $processorMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->processorMock = $this->createMock(MessageProcessor::class);
        $this->configMock = $this->createMock(MessageQueueConfig::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->customSubscriber = new CustomSubscriber($this->messageBusMock, $this->processorMock, $this->configMock, $this->loggerMock);
    }

    public function testCustomProcessingLogicGroupsMessagesCorrectly(): void
    {
        $eventMessage1 = new EventMessage(1, 'Task updated');
        $eventMessage2 = new EventMessage(1, 'Another update');

        $this->customSubscriber->onMessageDispatch($eventMessage1);
        $this->customSubscriber->onMessageDispatch($eventMessage2);

        $this->processorMock
            ->expects($this->once())
            ->method('sendGroupedMessage')
            ->with(1, [$eventMessage1, $eventMessage2]);

        $this->customSubscriber->processGroupedMessages();
    }
}
