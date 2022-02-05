<?php

interface EventStoreInterface
{
    public function loadFromStream(AggregateIdInterface $identity): EventStream;

    public function appendToStream(AggregateIdInterface $identity, Version $expectedVersion, EventCollection $events): void;
}