<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Example\Event;

use DateTimeImmutable;
use Tcieslar\EventStore\Event\EventId;
use Tcieslar\EventStore\Event\EventInterface;

abstract class DomainEventExample implements EventInterface
{
    protected EventId $eventId;
    protected DateTimeImmutable $occurredAt;

    public function __construct(
        ?EventId           $eventId,
        ?DateTimeImmutable $occurredAt
    )
    {
        $this->eventId = $eventId ?? new EventId();
        $this->occurredAt = $occurredAt ?? DateTimeImmutable::createFromFormat(
                DATE_RFC3339,
                (new \DateTimeImmutable())->format(DATE_RFC3339)
            );
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