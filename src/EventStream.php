<?php

class EventStream
{
    public function __construct(
        public readonly int $version,
        public readonly array $events
    )
    {
    }
}