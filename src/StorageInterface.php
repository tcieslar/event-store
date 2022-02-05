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

    public function storeEvent(AggregateIdInterface $aggregateId, Version $version, EventInterface $event): void;

    public function getAllEvents(): EventCollection;
}