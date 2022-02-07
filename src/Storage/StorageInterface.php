<?php

namespace Storage;

use Aggregate\AggregateIdInterface;
use Aggregate\Version;
use Event\EventCollection;
use Event\EventStream;

interface StorageInterface
{
    public function getAggregateVersion(AggregateIdInterface $aggregateId): ?Version;

    public function createAggregate(AggregateIdInterface $aggregateId, Version $expectedVersion): void;

    public function getEventStream(AggregateIdInterface $aggregateId): EventStream;

    public function getEventStreamAfterVersion(AggregateIdInterface $aggregateId, Version $afterVersion): EventStream;

    public function storeEvents(AggregateIdInterface $aggregateId, Version $version, EventCollection $events): Version;

    public function getAllEvents(): EventCollection;
}