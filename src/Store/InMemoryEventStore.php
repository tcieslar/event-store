<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Store;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\EventPublisher\EventPublisherInterface;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\EventStoreInterface;
use Tcieslar\EventStore\Exception\AggregateNotFoundException;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventSourcing\Uuid;

class InMemoryEventStore implements EventStoreInterface
{
    public function __construct(
        private InMemoryEventStorage    $storage,
        private EventPublisherInterface $eventPublisher
    )
    {
    }

    public function loadFromStream(Uuid $aggregateId, ?Version $afterVersion = null): EventStream
    {
        if (!$this->storage->getAggregateVersion($aggregateId)) {
            throw new AggregateNotFoundException($aggregateId);
        }

        if (!$afterVersion) {
            return $this->storage->getEventStream($aggregateId);
        }

        return $this->storage->getEventStreamAfterVersion($aggregateId, $afterVersion);
    }

    /**
     * @return Version new version
     * @throws ConcurrencyException
     */
    public function appendToStream(Uuid $aggregateId, AggregateType $aggregateType, Version $expectedVersion, EventCollection $events): Version
    {
        $actualVersion = $this->storage->getAggregateVersion($aggregateId);
        if (!$actualVersion) {
            $this->storage->createAggregate($aggregateId, $aggregateType, $expectedVersion);
            $actualVersion = $expectedVersion;
        }

        if (!$expectedVersion->isEqual($actualVersion)) {
            $newEventsStream = $this->loadFromStream($aggregateId, $expectedVersion);
            throw new ConcurrencyException($aggregateId, $aggregateType, $expectedVersion, $actualVersion, $events, $newEventsStream->events);
        }

        $newVersion = $this->storage->storeEvents($aggregateId, $actualVersion, $events);
        $this->eventPublisher->publish($events);
        return $newVersion;
    }

    public function getAllEvents(): EventCollection
    {
        return $this->storage->getAllEvents();
    }
}