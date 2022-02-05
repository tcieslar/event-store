<?php

class InMemoryStorage implements StorageInterface
{
    private array $aggregatesVersion = [];
    private array $events = [];

    public function getAggregateVersion(AggregateIdInterface $aggregateId): ?Version
    {
        $idString = $aggregateId->toString();
        return $this->aggregatesVersion[$idString] ?? null;
    }

    public function createAggregate(AggregateIdInterface $aggregateId, Version $expectedVersion): void
    {
        $idString = $aggregateId->toString();

        $this->aggregatesVersion[$idString] = $expectedVersion;
        $this->events[$idString] = [];
    }

    public function getEventStream(AggregateIdInterface $aggregateId): EventStream
    {
        $idString = $aggregateId->toString();
        $versionColumn = array_column($this->events[$idString], 'version');
        $eventsColumn = array_column($this->events[$idString], 'event');
        array_multisort($versionColumn, $eventsColumn, SORT_ASC);

        return new EventStream(
            aggregateId: $aggregateId,
            startVersion: Version::createFirstVersion(),
            endVersion: $this->aggregatesVersion[$idString],
            events: new EventCollection($eventsColumn)
        );
    }

    public function getEventStreamAfterVersion(AggregateIdInterface $aggregateId, Version $afterVersion): EventStream
    {
        $position = -1;
        foreach ($this->events[$aggregateId->toString()] as $key => $eventArray) {
            if ($eventArray['version'] > (int)$afterVersion->toString()) {
                $position = $key;
                break;
            }
        }

        if ($position === -1) {
            throw new RuntimeException('Wrong version provided.');
        }
        $events = array_slice($this->events[$aggregateId->toString()], $position);

        return new EventStream(
            $aggregateId,
            Version::createVersion(current($events)['version']),
            $this->aggregatesVersion[$aggregateId->toString()],
            new EventCollection(array_column($events,'event'))
        );
    }

    public function storeEvent(AggregateIdInterface $aggregateId, Version $version, EventInterface $event): void
    {
        $idString = $aggregateId->toString();

        $this->events[$idString][] = [
            'version' => (int)$version->toString(),
            'occurred_at' => $event->occurredAt(),
            'event' => $event
        ];

        $this->aggregatesVersion[$idString] = $version;
    }

    public function getAllEvents(): EventCollection
    {
        $result = [];
        foreach ($this->events as $aggregate) {
            $result = [...array_column($aggregate, 'event')];
        }

        return new EventCollection($result);
    }
}