<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Event;

use DateTimeImmutable;
use Tcieslar\EventSourcing\Event;
use Tcieslar\EventSourcing\Uuid;

abstract class DomainEvent implements Event
{
    protected Uuid $eventId;

    protected DateTimeImmutable $occurredAt;

    public function __construct(
        ?Uuid              $eventId,
        ?DateTimeImmutable $occurredAt
    )
    {
        $this->eventId = $eventId ?? Uuid::random();
        $this->occurredAt = $occurredAt ?? DateTimeImmutable::createFromFormat(
                DATE_RFC3339,
                (new \DateTimeImmutable())->format(DATE_RFC3339)
            );
    }

    public function getEventId(): Uuid
    {
        return $this->eventId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}