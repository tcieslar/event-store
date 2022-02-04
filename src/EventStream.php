<?php

class EventStream
{
    public function __construct(
        public readonly Version $version,
        public readonly EventCollection $events
    )
    {
    }

    public function isEmpty(): bool
    {
        return empty($this->events);
    }
}