<?php

namespace Tcieslar\EventStore\Store;

use Psr\Log\LoggerInterface;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventSourcing\Uuid;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\EventStoreInterface;

class LoggableEventStore implements EventStoreInterface
{
    private EventStoreInterface $eventStore;
    private LoggerInterface $logger;

    public function __construct(EventStoreInterface $eventStore, LoggerInterface $logger)
    {
        $this->eventStore = $eventStore;
        $this->logger = $logger;
    }

    public function loadFromStream(Uuid $aggregateId, ?Version $afterVersion = null): EventStream
    {
        $eventStream = $this->eventStore->loadFromStream($aggregateId, $afterVersion);
        $this->logger->debug("Event stream (Aggregate - {$eventStream->aggregateType->toString()} - {$aggregateId->toString()}) loaded. Loaded version {$eventStream->endVersion->toString()}, after version {$afterVersion?->toString()}.", [
            'aggregate_id' => $aggregateId->toString(),
            'aggregate_type' => $eventStream->aggregateType->toString(),
            'after_version' => (int)$afterVersion?->toString(),
            'start_version' => (int)$eventStream->startVersion->toString(),
            'end_version' => (int)$eventStream->endVersion->toString(),
            'events_count' => $eventStream->events->count()
        ]);

        return $eventStream;
    }

    public function appendToStream(Uuid $aggregateId, AggregateType $aggregateType, Version $expectedVersion, EventCollection $events): Version
    {
        $version = $this->eventStore->appendToStream($aggregateId, $aggregateType, $expectedVersion, $events);

        $this->logger->debug("Event stream (Aggregate - {$aggregateType->toString()} - {$aggregateId->toString()}) saved. Expected version {$expectedVersion->toString()}, events count {$events->count()}.", [
            'aggregate_id' => $aggregateId->toString(),
            'aggregate_type' => $aggregateType->toString(),
            'expected_version' => (int)$expectedVersion->toString(),
            'events_count' => $events->count()
        ]);

        return $version;
    }
}