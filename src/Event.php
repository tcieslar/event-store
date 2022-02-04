<?php

abstract class Event implements EventInterface
{
    public function __construct(
        public readonly \DateTimeImmutable $occurredAt = new DateTimeImmutable()
    )
    {
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getType(): string
    {
        return static::class;
    }
}