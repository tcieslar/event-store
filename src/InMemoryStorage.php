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
            startVersion: Version::createZeroVersion(),
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
            new EventCollection(array_column($events, 'event'))
        );
    }

    public function storeEvents(AggregateIdInterface $aggregateId, Version $version, EventCollection $events): Version
    {
        $idString = $aggregateId->toString();
        $newVersion = $version;
        /** @var EventInterface $event */
        foreach ($events->getAll() as $event) {
            $newVersion = $newVersion->incremented();
            $this->events[$idString][] = [
                'version' => (int)$newVersion->toString(),
                'occurred_at' => $event->getOccurredAt(),
                'event' => $event
            ];
            $this->aggregatesVersion[$idString] = $newVersion;
        }
        return $newVersion;
    }

    public function getAllEvents(): EventCollection
    {
        $result = [];
        foreach ($this->events as $aggregate) {
            foreach ($aggregate as $item) {
                $result[] = $item['event'];
            }
        }

        return new EventCollection($result);
    }
}