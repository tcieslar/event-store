<?php

namespace Tcieslar\EventStore\Store;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\EventPublisher\EventPublisherInterface;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\EventStoreInterface;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventStore\Store\EventStorageInterface;

class InMemoryEventStore implements EventStoreInterface
{
    public function __construct(
        private EventStorageInterface   $storage,
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

        $newVersion = $this->storage->storeEvents($aggregateId, $version, $events);
        $this->eventPublisher->publish($events);
        return $newVersion;
    }

    public function getAllEvents(): EventCollection
    {
        return $this->storage->getAllEvents();
    }
}