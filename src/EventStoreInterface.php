<?php declare(strict_types=1);

namespace Tcieslar\EventStore;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\Exception\ConcurrencyException;

interface EventStoreInterface
{
    public function loadFromStream(AggregateIdInterface $aggregateId, ?Version $afterVersion = null): EventStream;

    /**
     * @throws ConcurrencyException
     */
    public function appendToStream(AggregateIdInterface $aggregateId, AggregateType $aggregateType, Version $expectedVersion, EventCollection $events): Version;
}