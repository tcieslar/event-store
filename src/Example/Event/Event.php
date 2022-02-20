<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Example\Event;

use DateTimeImmutable;
use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Event\EventType;

abstract class Event implements EventInterface
{
    public readonly EventType $eventType;
    public readonly DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->eventType = new EventType(static::class);
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}