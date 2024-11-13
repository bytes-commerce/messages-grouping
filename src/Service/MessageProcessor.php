<?php

namespace App\Service;

class MessageProcessor
{
    public function sendGroupedMessage(int $taskId, array $messages): bool
    {
        try{
            if (empty($messages)) {
                return true;
            }

            $messageContent = $this->createSummaryContent($messages);
            $this->sendEmail($taskId, $messageContent);

            return true;
        } catch (\Exception $e) {
            echo "Failed, something went wrong!";
            return false;
        };
    }

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
        echo "Sending email for Task ID $taskId:\n$content";
    }
}