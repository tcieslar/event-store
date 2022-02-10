<?php

namespace Tcieslar\EventStore\Example\Event;

use DateTimeImmutable;
use Tcieslar\EventStore\Event\EventInterface;

abstract class Event implements EventInterface
{
    public readonly string $eventClass;
    public readonly DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->eventClass = static::class;
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getEventClass(): string
    {
        return $this->eventClass;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}