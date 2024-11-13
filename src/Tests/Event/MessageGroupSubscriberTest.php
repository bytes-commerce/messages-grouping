<?php

use App\Config\MessageQueueConfig;
use PHPUnit\Framework\TestCase;
use App\Event\MessageGroupSubscriber;
use App\Event\EventMessage;
use App\Service\MessageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageGroupSubscriberTest extends TestCase
{
    private MessageGroupSubscriber $subscriber;
    private $messageBusMock;
    private $processorMock;
    private $loggerMock;
    private $configMock;


    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);
        $this->processorMock = $this->createMock(MessageProcessor::class);
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

        // Check that the message queue contains the dispatched message
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
            ->expects($this->once())
            ->method('sendGroupedMessage')
            ->with(1, [$eventMessage1, $eventMessage2]);

        $this->subscriber->processGroupedMessages();

        $this->assertEmpty($this->subscriber->getMessageQueue());
    }

    public function testEmptyQueueDoesNoProcessMessages(): void
    {
        $this->processorMock
            ->expects($this->never())
            ->method('sendGroupedMessage');
        $this->subscriber->processGroupedMessages();
    }


    public function testInvalidMessageIgnored(): void
    {
        $invalidMessage = new class() {};

        $this->expectException(\TypeError::class);
        $this->subscriber->onMessageDispatch($invalidMessage);
    }

    public function testSendGroupedMessageFailureHandled(): void
    {
        $eventMessage = new EventMessage(1, "Task updated");
        $this->assertEmpty($this->subscriber->onMessageDispatch($eventMessage));

        $this->processorMock
            ->method('sendGroupedMessage')
            ->willThrowException(new \RuntimeException("Message sending failed"));

        try{
            $this->subscriber->processGroupedMessages();
        } catch (\RuntimeException $e) {
            $this->assertEquals("Message sending failed", $e->getMessage());
        }
    }

    public function testProcessGroupedMessagesWithCustomLogic(): void
    {
        $customLogic = function (array $messageQueue) {
            foreach ($messageQueue as $taskId => $messages) {
                echo "Processed task $taskId with " . count($messages) . " messages.";
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
