<?php

interface EventStoreInterface
{
    public function loadFromStream(AggregateIdInterface $aggregateId): EventStream;

    public function appendToStream(AggregateIdInterface $aggregateId, Version $expectedVersion, EventCollection $events): void;
}