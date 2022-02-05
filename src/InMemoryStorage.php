<?php

class InMemoryStorage implements StorageInterface
{
    private array $aggregatesVersion = [];
    private array $events = [];

    public function getAggregateVersion(AggregateIdInterface $id): ?Version
    {
        $idString = $id->toString();
        return $this->aggregatesVersion[$idString] ?? null;
    }

    public function createAggregate(AggregateIdInterface $id, Version $expectedVersion): void
    {
        $idString = $id->toString();

        $this->aggregatesVersion[$idString] = $expectedVersion;
        $this->events[$idString] = [];
    }

    public function getEventStream(AggregateIdInterface $id): EventStream
    {
        $idString = $id->toString();
        $versionColumn = array_column($this->events[$idString], 'version');
        $eventsColumn = array_column($this->events[$idString], 'event');
        array_multisort($versionColumn, $eventsColumn, SORT_ASC);

        return new EventStream(
            version: $this->aggregatesVersion[$idString],
            events: new EventCollection($eventsColumn)
        );
    }

    public function storeEvent(AggregateIdInterface $id, Version $version, EventInterface $event): void
    {
        $idString = $id->toString();

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