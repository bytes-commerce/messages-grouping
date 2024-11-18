<?php

namespace MessagesGrouping\Service;

use MessagesGrouping\Event\EventMessage;

interface MessageProcessorInterface
{
    /**
     * Sends a grouped message for a specific task ID.
     *
     * @param int $taskId The ID of the task.
     * @param array<EventMessage> $messages An array of event messages to be grouped.
     *
     * @return bool True if the message was sent successfully, false otherwise.
     */
    public function sendGroupedMessage(int $taskId, array $messages): bool;


//    /**
//     * @param array<EventMessage> $messages;
//     */
//    public function createSummaryContent(array $messages): string;
//
//    public function sendEmail(int $taskId, string $content): void;
}
