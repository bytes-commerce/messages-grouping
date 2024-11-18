<?php

namespace MessagesGrouping\Event;

class EventMessage
{
    private readonly int $taskId;

    private readonly string $content;

    public function __construct(int $taskId, ?string $content = '')
    {
        $this->taskId = $taskId;
        $this->content = $content ?? '';
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
