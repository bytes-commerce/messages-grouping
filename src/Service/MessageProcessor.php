<?php

namespace MessagesGrouping\Service;

use MessagesGrouping\Event\EventMessage;

final class MessageProcessor implements MessageProcessorInterface
{

    /**
     * @param array<EventMessage> $messages
     */
    public function sendGroupedMessage(int $taskId, array $messages): bool
    {
        try {
            if ($messages === []) {
                return true;
            }

            $messageContent = $this->createSummaryContent($messages);
            $this->sendEmail($taskId, $messageContent);

            return true;
        } catch (\Exception $exception) {
            echo "Failed, something went wrong!";
            return false;
        }
    }

    /**
     * @param array<EventMessage> $messages
     */
    private function createSummaryContent(array $messages): string
    {
        $content = "Task Status Updates:\n";
        foreach ($messages as $message) {
            $content .= $message->getContent()."\n";
        }

        return $content;
    }

    private function sendEmail(int $taskId, string $content): void
    {
        echo "Sending email for Task ID {$taskId}:\n{$content}";
    }
}
