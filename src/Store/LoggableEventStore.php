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
        $this->logger->debug('Load from event stream.', [
            'aggregate_id' => $aggregateId->toString(),
            'aggregate_type' => $eventStream->aggregateType,
            'after_version' => $afterVersion?->toString(),
            'start_version' => $eventStream->startVersion->toString(),
            'end_version' => $eventStream->endVersion->toString(),
            'events_count' => $eventStream->events->count()
        ]);

        return $eventStream;
    }

    public function appendToStream(Uuid $aggregateId, AggregateType $aggregateType, Version $expectedVersion, EventCollection $events): Version
    {
        $version = $this->eventStore->appendToStream($aggregateId, $aggregateType, $expectedVersion, $events);

        $this->logger->debug('Save to event stream.', [
            'aggregate_id' => $aggregateId->toString(),
            'aggregate_type' => $aggregateType,
            'expected_version' => $expectedVersion->toString(),
            'events_count' => $events->count()
        ]);

        return $version;
    }
}