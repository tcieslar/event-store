<?php

interface StorageInterface
{
    /**
     * If aggregate doesn't exists return null
     *
     * @param AggregateIdInterface $aggregateId
     * @return Version|null
     */
    public function getAggregateVersion(AggregateIdInterface $aggregateId): ?Version;

    public function createAggregate(AggregateIdInterface $aggregateId, Version $expectedVersion): void;

    public function getEventStream(AggregateIdInterface $aggregateId): EventStream;

    public function getEventStreamAfterVersion(AggregateIdInterface $aggregateId, Version $afterVersion): EventStream;

    public function storeEvents(AggregateIdInterface $aggregateId, Version $version, EventCollection $events): Version;

    public function getAllEvents(): EventCollection;
}