<?php

namespace Example;

use EventCollection;
use EventInterface;
use EventStoreInterface;
use EventStream;
use AggregateIdInterface;
use StorageInterface;
use Version;

class EventStore implements EventStoreInterface
{
    public function __construct(
        private StorageInterface $storage
    )
    {
    }

    public function loadFromStream(AggregateIdInterface $identity): EventStream
    {
        if (!$this->storage->getAggregateVersion($identity)) {
            throw new \InvalidArgumentException('Aggregate not found.');
        }

        return $this->storage->getEventStream($identity);
    }

    public function appendToStream(AggregateIdInterface $identity, Version $expectedVersion, EventCollection $events): void
    {
        $version = $this->storage->getAggregateVersion($identity);
        if (!$version) {
            $this->storage->createAggregate($identity, $expectedVersion);
            $version = $expectedVersion;
        }

        if (!$expectedVersion->isEqual($version)) {
            throw new \RuntimeException('Concurrency error.');
        }

        /** @var EventInterface $event */
        foreach ($events as $event) {
            $expectedVersion = $expectedVersion->incremented();
            $this->storage->storeEvent($identity, $expectedVersion, $event);
        }
    }

    public function getAllEvents(): EventCollection
    {
        return $this->storage->getAllEvents();
    }
}