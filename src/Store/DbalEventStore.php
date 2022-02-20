<?php

namespace Tcieslar\EventStore\Store;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\EventStoreInterface;

class DbalEventStore implements EventStoreInterface
{

    public function loadFromStream(AggregateIdInterface $aggregateId, ?Version $afterVersion = null): EventStream
    {
        // TODO: Implement loadFromStream() method.
    }

    public function appendToStream(AggregateIdInterface $aggregateId, AggregateType $aggregateType, Version $expectedVersion, EventCollection $events): Version
    {
        // TODO: Implement appendToStream() method.
    }
}