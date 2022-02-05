<?php

interface StorageInterface
{
    /**
     * If aggregate doesn't exists return null
     *
     * @param AggregateIdInterface $id
     * @return Version|null
     */
    public function getAggregateVersion(AggregateIdInterface $id): ?Version;

    public function createAggregate(AggregateIdInterface $id, Version $expectedVersion): void;

    public function getEventStream(AggregateIdInterface $id): EventStream;

    public function storeEvent(AggregateIdInterface $id, Version $version, EventInterface $event): void;

    public function getAllEvents(): EventCollection;
}