<?php

namespace Example;

use EventCollection;
use EventInterface;
use EventStoreInterface;
use EventStream;
use AggregateIdInterface;
use Version;

class EventStoreInMemory implements EventStoreInterface
{
    private array $events = [];
    private array $aggregatesVersion = [];

    public function __construct()
    {
    }

    public function loadEventStream(AggregateIdInterface $identity): EventStream
    {
        $id = $identity->toString();
        if (!isset($this->aggregatesVersion[$id])) {
            throw new \InvalidArgumentException('Aggregate not found.');
        }

        $versionColumn = array_column($this->events[$id], 'version');
        $eventsColumn = array_column($this->events[$id], 'event');
        array_multisort($versionColumn, $eventsColumn, SORT_ASC);
        return new EventStream(
            version: $this->aggregatesVersion[$id],
            events: new EventCollection($eventsColumn)
        );
    }

    public function appendToStream(AggregateIdInterface $identity, Version $expectedVersion, EventCollection $events): void
    {
        $id = $identity->toString();
        if (!isset($this->aggregatesVersion[$id])) {
            $this->aggregatesVersion[$id] = $expectedVersion;
            $this->events[$id] = [];
        }
        /** @var Version $version */
        $version = $this->aggregatesVersion[$id];
        if (!$expectedVersion->isEqual($version)) {
            throw new \RuntimeException('Concurrency error.');
        }

        /** @var EventInterface $event */
        foreach ($events as $event) {
            $version = $version->incremented();
            $this->events[$id][] = [
                'version' => (int)$version->toString(),
                'occurred_at' => $event->occurredAt(),
                'event' => $event
            ];
        }
        $this->aggregatesVersion[$id] = $version;
    }

    public function getAllEvents(): array
    {
        $result = [];
        foreach ($this->events as $aggregate) {
            $result = [...$aggregate];
        }
        return $result;
    }
}