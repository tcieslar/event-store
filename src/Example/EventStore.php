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

    public function loadFromStream(AggregateIdInterface $aggregateId, ?Version $afterVersion = null): EventStream
    {
        if (!$this->storage->getAggregateVersion($aggregateId)) {
            throw new \InvalidArgumentException('Aggregate not found.');
        }

        if (!$afterVersion) {
            return $this->storage->getEventStream($aggregateId);
        }

        return $this->storage->getEventStreamAfterVersion($aggregateId, $afterVersion);
    }

    /**
     * @throws ConcurrencyException
     */
    public function appendToStream(AggregateIdInterface $aggregateId, Version $expectedVersion, EventCollection $events): Version
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

        return $version;
    }

    public function getAllEvents(): EventCollection
    {
        return $this->storage->getAllEvents();
    }
}