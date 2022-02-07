<?php

use JetBrains\PhpStorm\Pure;

class EventStream
{
    public function __construct(
        public readonly AggregateIdInterface $aggregateId,
        public readonly Version $startVersion,
        public readonly Version $endVersion,
        public readonly EventCollection $events
    )
    {
    }

    #[Pure] public function isEmpty(): bool
    {
        return $this->events->count() === 0;
    }
}