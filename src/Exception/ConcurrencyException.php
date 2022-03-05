<?php

namespace Tcieslar\EventStore\Exception;

use Exception;
use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Utils\Uuid;

class ConcurrencyException extends Exception
{
    public function __construct(
        public readonly Uuid $aggregateId,
        public readonly AggregateType $aggregateType,
        public readonly Version $expectedVersion,
        public readonly Version $actualVersion,
        public readonly EventCollection $eventsToStore,
        public readonly EventCollection $storedEvents,
    )
    {
        parent::__construct();
    }
}