<?php

interface EventStoreInterface
{
    public function loadFromStream(AggregateIdInterface $aggregateId, ?Version $afterVersion = null): EventStream;

    public function appendToStream(AggregateIdInterface $aggregateId, Version $expectedVersion, EventCollection $events): Version;
}