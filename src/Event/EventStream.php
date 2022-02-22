<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use JetBrains\PhpStorm\Pure;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;

class EventStream
{
    public function __construct(
        public readonly AggregateIdInterface $aggregateId,
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