<?php

namespace MessagesGrouping\Tests\Event;

use MessagesGrouping\Config\MessageQueueConfig;
use MessagesGrouping\Event\CustomSubscriber;
use MessagesGrouping\Event\EventMessage;
use MessagesGrouping\Service\MessageProcessor;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CustomSubscriberTest extends TestCase
{

    public MockObject $configMock;

    private MockObject $loggerMock;

    private CustomSubscriber $customSubscriber;

    private MockObject $messageBusMock;

    private MessageProcessor $processorMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->processorMock = new MessageProcessor();
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

        $this->assertTrue($this->processorMock
            ->sendGroupedMessage(1, [$eventMessage1, $eventMessage2]));

        $this->customSubscriber->processGroupedMessages();
    }
}
