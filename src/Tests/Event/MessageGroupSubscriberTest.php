<?php

use MessagesGrouping\Config\MessageQueueConfig;
use PHPUnit\Framework\TestCase;
use MessagesGrouping\Event\MessageGroupSubscriber;
use MessagesGrouping\Event\EventMessage;
use MessagesGrouping\Service\MessageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageGroupSubscriberTest extends TestCase
{
    private MessageGroupSubscriber $subscriber;

    private MessageBusInterface $messageBusMock;

    private MessageProcessor $processorMock;

    private LoggerInterface $loggerMock;

    private MessageQueueConfig $configMock;


    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->processorMock = new MessageProcessor();
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(MessageQueueConfig::class);

        $this->subscriber = new MessageGroupSubscriber(
            $this->messageBusMock,
            $this->processorMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    public function testOnMessageDispatchQueuesMessage(): void
    {
        $eventMessage = new EventMessage(1, "Task updated");
        $this->subscriber->onMessageDispatch($eventMessage);

        $messageQueue = (new \ReflectionClass($this->subscriber))->getProperty('messageQueue');
        $messageQueue->setAccessible(true);

        $queuedMessages = $messageQueue->getValue($this->subscriber);

        $this->assertArrayHasKey(1, $queuedMessages);
        $this->assertCount(1, $queuedMessages[1]);
        $this->assertSame($eventMessage, $queuedMessages[1][0]);
    }

    public function testProcessGroupedMessagesSendsGroupedMessages(): void
    {
        $eventMessage1 = new EventMessage(1, "Task updated");
        $eventMessage2 = new EventMessage(1, "Another update");

        $this->subscriber->onMessageDispatch($eventMessage1);
        $this->subscriber->onMessageDispatch($eventMessage2);

        $this->processorMock
            ->sendGroupedMessage(1, []);

        $this->subscriber->processGroupedMessages();

        $this->assertEmpty($this->subscriber->getMessageQueue());
    }

    public function testEmptyQueueDoesNoProcessMessages(): void
    {
        $this->assertTrue($this->processorMock
            ->sendGroupedMessage(1, []));
        $this->subscriber->processGroupedMessages();
    }


    //    public function testInvalidMessageIgnored(): void
    //    {
    //        $invalidMessage = new \stdClass();
    //
    //        $this->expectException(\TypeError::class);
    //        $this->subscriber->onMessageDispatch($invalidMessage);
    //    }

    public function testSendGroupedMessageFailureHandled(): void
    {
        $eventMessage = new EventMessage(1, "Task updated");
        $this->subscriber->onMessageDispatch($eventMessage);

        $this->assertTrue($this->processorMock
            ->sendGroupedMessage(1, []));

        try {
            $this->subscriber->processGroupedMessages();
        } catch (\RuntimeException $runtimeException) {
            $this->assertEquals("Message sending failed", $runtimeException->getMessage());
        }
    }

    public function testProcessGroupedMessagesWithCustomLogic(): void
    {
        $customLogic = function (array $messageQueue): void {
            foreach ($messageQueue as $taskId => $messages) {
                echo sprintf('Processed task %s with ', $taskId) . count($messages) . " messages.";
            }
        };

        $this->subscriber = new MessageGroupSubscriber(
            $this->messageBusMock,
            $this->processorMock,
            $this->configMock,
            $this->loggerMock,
            $customLogic
        );

        $eventMessage = new EventMessage(1, "Task update");
        $this->subscriber->onMessageDispatch($eventMessage);

        $this->expectOutputString("Processed task 1 with 1 messages.");
        $this->subscriber->processGroupedMessages();
    }
}
