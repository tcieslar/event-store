<?php

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

    public function isEmpty(): bool
    {
        return empty($this->events);
    }
}