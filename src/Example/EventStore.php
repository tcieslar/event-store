<?php

namespace Example;

use EventCollection;
use EventInterface;
use ConcurrencyException;
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

    public function loadFromStream(AggregateIdInterface $aggregateId): EventStream
    {
        if (!$this->storage->getAggregateVersion($aggregateId)) {
            throw new \InvalidArgumentException('Aggregate not found.');
        }

        return $this->storage->getEventStream($aggregateId);
    }

    /**
     * @throws ConcurrencyException
     */
    public function appendToStream(AggregateIdInterface $aggregateId, Version $expectedVersion, EventCollection $events): void
    {
        $version = $this->storage->getAggregateVersion($aggregateId);
        if (!$version) {
            $this->storage->createAggregate($aggregateId, $expectedVersion);
            $version = $expectedVersion;
        }

        if (!$expectedVersion->isEqual($version)) {
            $storedEvents = new EventCollection();
            throw new ConcurrencyException($aggregateId, $expectedVersion, $events, $storedEvents);
        }

        /** @var EventInterface $event */
        foreach ($events as $event) {
            $version = $version->incremented();
            $this->storage->storeEvent($aggregateId, $version, $event);
        }
    }

    public function getAllEvents(): EventCollection
    {
        return $this->storage->getAllEvents();
    }
}