<?php

namespace Example;

use EventCollection;
use ConcurrencyException;
use EventPublisherInterface;
use EventStoreInterface;
use EventStream;
use AggregateIdInterface;
use StorageInterface;
use Version;

class EventStore implements EventStoreInterface
{
    public function __construct(
        private StorageInterface $storage,
        private EventPublisherInterface $eventPublisher
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
     * @return Version new version
     */
    public function appendToStream(AggregateIdInterface $aggregateId, Version $expectedVersion, EventCollection $events): Version
    {
        $version = $this->storage->getAggregateVersion($aggregateId);
        if (!$version) {
            $this->storage->createAggregate($aggregateId, $expectedVersion);
            $version = $expectedVersion;
        }

        if (!$expectedVersion->isEqual($version)) {
            $newEventsStream = $this->loadFromStream($aggregateId, $expectedVersion);
            throw new ConcurrencyException($aggregateId, $expectedVersion, $events, $newEventsStream->events);
        }

        $version2 = $this->storage->storeEvents($aggregateId, $version, $events);
        $this->eventPublisher->publish($events);
        return $version2;
    }

    public function getAllEvents(): EventCollection
    {
        return $this->storage->getAllEvents();
    }
}