<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Event;

use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventSourcing\Uuid;

class EventStream
{
    public function __construct(
        public readonly Uuid $aggregateId,
        public readonly AggregateType $aggregateType,
        public readonly Version $startVersion,
        public readonly Version $endVersion,
        public readonly EventCollection $events
    )
    {
    }

    public function isEmpty(): bool
    {
        return $this->events->count() === 0;
    }
}