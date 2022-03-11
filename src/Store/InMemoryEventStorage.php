<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Store;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventSourcing\Event;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventSourcing\Uuid;

class InMemoryEventStorage
{
    private array $aggregatesVersion = [];
    private array $aggregatesType = [];
    private array $events = [];

    public function getAggregateVersion(Uuid $aggregateId): ?Version
    {
        $idString = $aggregateId->toString();
        return $this->aggregatesVersion[$idString] ?? null;
    }

    public function getAggregateType(Uuid $aggregateId): ?AggregateType
    {
        $idString = $aggregateId->toString();
        return $this->aggregatesType[$idString] ?? null;
    }

    public function createAggregate(Uuid $aggregateId, AggregateType $aggregateType, Version $expectedVersion): void
    {
        $idString = $aggregateId->toString();

        $this->aggregatesVersion[$idString] = $expectedVersion;
        $this->aggregatesType[$idString] = $aggregateType;
        $this->events[$idString] = [];
    }

    public function getEventStream(Uuid $aggregateId): EventStream
    {
        $idString = $aggregateId->toString();
        $versionColumn = array_column($this->events[$idString], 'version');
        $eventsColumn = array_column($this->events[$idString], 'event');
        array_multisort($versionColumn, $eventsColumn, SORT_ASC);

        return new EventStream(
            aggregateId: $aggregateId,
            aggregateType: $this->getAggregateType($aggregateId),
            startVersion: Version::zero(),
            endVersion: $this->aggregatesVersion[$idString],
            events: new EventCollection($eventsColumn)
        );
    }

    public function getEventStreamAfterVersion(Uuid $aggregateId, Version $afterVersion): EventStream
    {
        $events = [];
        foreach ($this->events[$aggregateId->toString()] as $key => $eventArray) {
            if ($eventArray['version'] > (int)$afterVersion->toString()) {
                $events[] = $eventArray;

            }
        }

        $aggregateVersion = $this->aggregatesVersion[$aggregateId->toString()];
        return new EventStream(
            $aggregateId,
            $this->getAggregateType($aggregateId),
            !empty($events) ?
                Version::number(current($events)['version']) :
                $aggregateVersion,
            $aggregateVersion,
            new EventCollection(array_column($events, 'event'))
        );
    }

    public function storeEvents(Uuid $aggregateId, Version $version, EventCollection $events): Version
    {
        $idString = $aggregateId->toString();
        $newVersion = $version;
        /** @var Event $event */
        foreach ($events->getAll() as $event) {
            $newVersion = $newVersion->incremented();
            $this->events[$idString][] = [
                'version' => (int)$newVersion->toString(),
                'occurred_at' => $event->getOccurredAt(),
                'event' => $event,
                'type' => get_class($event)
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