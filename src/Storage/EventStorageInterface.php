<?php

namespace Tcieslar\EventStore\Storage;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventStream;

interface EventStorageInterface
{
    public function getAggregateVersion(AggregateIdInterface $aggregateId): ?Version;

    public function createAggregate(AggregateIdInterface $aggregateId, Version $expectedVersion): void;

    public function getEventStream(AggregateIdInterface $aggregateId): EventStream;

    public function getEventStreamAfterVersion(AggregateIdInterface $aggregateId, Version $afterVersion): EventStream;

    public function storeEvents(AggregateIdInterface $aggregateId, Version $version, EventCollection $events): Version;

    public function getAllEvents(): EventCollection;
}