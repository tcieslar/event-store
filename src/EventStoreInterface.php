<?php

interface EventStoreInterface
{
    public function loadFromStream(AggregateIdInterface $aggregateId, ?Version $afterVersion = null): EventStream;

    /**
     * @throws ConcurrencyException
     */
    public function appendToStream(AggregateIdInterface $aggregateId, Version $expectedVersion, EventCollection $events): Version;
}