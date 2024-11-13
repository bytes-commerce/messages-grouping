<?php

namespace App\Event;

class EventMessage
{
    private int $taskId;
    private string $content;

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
