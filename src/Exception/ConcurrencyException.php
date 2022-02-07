<?php

namespace Exception;

use Aggregate\AggregateIdInterface;
use Aggregate\Version;
use Event\EventCollection;
use Exception;

class ConcurrencyException extends Exception
{
    public function __construct(
        public readonly AggregateIdInterface $aggregateId,
        public readonly Version $expectedVersion,
        public readonly EventCollection $eventsToStore,
        public readonly EventCollection $storedEvents,
    )
    {
        parent::__construct();
    }
}