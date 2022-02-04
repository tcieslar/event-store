<?php

interface EventStoreInterface
{
    public function loadEventStream(AggregateIdInterface $identity): EventStream;

    public function appendToStream(AggregateIdInterface $identity, Version $expectedVersion, array $events): void;
}