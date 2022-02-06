<?php

namespace Example\Event;

use DateTimeImmutable;
use EventInterface;

abstract class Event implements EventInterface
{
    public readonly string $type;
    public readonly DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->type = static::class;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}