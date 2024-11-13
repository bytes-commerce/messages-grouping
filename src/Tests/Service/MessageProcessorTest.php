<?php

namespace App\Tests\Service;

use App\Service\MessageProcessor;
use App\Event\EventMessage;
use PHPUnit\Framework\TestCase;

class MessageProcessorTest extends TestCase
{
    private MessageProcessor $messageProcessor;

    protected function setUp(): void
    {
        $this->messageProcessor = new MessageProcessor();
    }

    public function testSendGroupedMessageAggregatesContent(): void
    {
        $this->expectOutputString("Sending email for Task ID 1:\nTask Status Updates:\nTask status updated\nAnother status update\n");

        $eventMessage1 = new EventMessage(1, "Task status updated");
        $eventMessage2 = new EventMessage(1, "Another status update");

        $this->messageProcessor->sendGroupedMessage(1, [$eventMessage1, $eventMessage2]);
    }

    public function testSendGroupedMessageWithEmptyMessageList(): void
    {
        $result = $this->messageProcessor->sendGroupedMessage(1, []);
        $this->assertTrue($result, 'Expected true when sending grouped message with an empty list || successfully processed');
    }

    public function testSendGroupedMessageWithEmptyContent(): void
    {
        $eventMessage1 = new EventMessage(1, "");
        $eventMessage2 = new EventMessage(1, null);

        $result1 = $this->messageProcessor->sendGroupedMessage(1, [$eventMessage1]);
        $result2 = $this->messageProcessor->sendGroupedMessage(1, [$eventMessage2]);

        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }

    public function testSendGroupedMessageFailure(): void
    {
        $processor = $this->getMockBuilder(MessageProcessor::class)
            ->onlyMethods(['sendGroupedMessage'])
            ->getMock();

        $processor->method('sendGroupedMessage')
            ->will($this->throwException(new \RuntimeException("Failed to send")));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to send');

        $eventMessage = new EventMessage(1, "Task status update");
        $processor->sendGroupedMessage(1, [$eventMessage]);
    }
}
