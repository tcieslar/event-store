<?php declare(strict_types=1);

namespace Tcieslar\EventStore;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\Exception\AggregateNotFoundException;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventSourcing\Uuid;

interface EventStoreInterface
{
    /**
     * @throws AggregateNotFoundException
     */
    public function loadFromStream(Uuid $aggregateId, ?Version $afterVersion = null): EventStream;

    /**
     * @throws ConcurrencyException
     */
    public function appendToStream(Uuid $aggregateId, AggregateType $aggregateType, Version $expectedVersion, EventCollection $events): Version;
}