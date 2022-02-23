<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Example\Event;

use DateTimeImmutable;
use Tcieslar\EventStore\Event\EventId;
use Tcieslar\EventStore\Event\EventInterface;

abstract class Event implements EventInterface
{
    protected EventId $eventId;
    protected DateTimeImmutable $occurredAt;

    public function __construct(
        ?EventId           $eventId,
        ?DateTimeImmutable $occurredAt
    )
    {
        $this->eventId = $eventId ?? new EventId();
        $this->occurredAt = $occurredAt ?? new \DateTimeImmutable();
    }

    public function getEventId(): EventId
    {
        return $this->eventId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    abstract public function normalize(): array;

    abstract public static function denormalize(array $data): static;
}