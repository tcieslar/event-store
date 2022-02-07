<?php

namespace Aggregate;

use RuntimeException;
use Snapshot\Snapshot;
use Exception\ConcurrencyException;
use ConcurrencyResolving\ConcurrencyResolvingStrategyInterface;
use EventStoreInterface;
use Snapshot\SnapshotRepositoryInterface;

class AggregateManager
{
    public function __construct(
        private UnitOfWork                            $unitOfWork,
        private EventStoreInterface                   $eventStore,
        private SnapshotRepositoryInterface           $snapshotRepository,
        private ConcurrencyResolvingStrategyInterface $concurrencyResolvingStrategy
    )
    {
    }

    public function addAggregate(Aggregate $aggregate): void
    {
        $this->unitOfWork->insert($aggregate);
    }

    public function findAggregate(string $className, AggregateIdInterface $aggregateId)
    {
        if ($aggregate = $this->unitOfWork->get($aggregateId)) {
            return $aggregate;
        }

        $snapshot = $this->snapshotRepository->getSnapshot($aggregateId);
        if (!$snapshot) {
            return $this->loadFromStore($aggregateId, $className);
        }

        return $this->loadFromSnapshot($aggregateId, $snapshot);
    }

    public function reset(): void
    {
        $this->unitOfWork->reset();
    }

    public function flush(): void
    {
        $identityMap = $this->unitOfWork->getIdentityMap();
        foreach ($identityMap as $row) {
            /** @var Aggregate $aggregate */
            $aggregate = $row['aggregate'];
            /** @var Version $version */
            $version = $row['version'];
            try {
                $newVersion = $this->eventStore->appendToStream($aggregate->getId(), $version, $aggregate->recordedEvents());
                $this->unitOfWork->changeVersion($aggregate, $newVersion);
                $aggregate->removeRecordedEvents();
            } catch (ConcurrencyException $exception) {
                $this->concurrencyResolvingStrategy->resolve($exception);
            }
        }
    }

    private function loadFromStore(AggregateIdInterface $aggregateId, string $className): mixed
    {
        $eventStream = $this->eventStore->loadFromStream($aggregateId);

        $aggregate = $className::loadFromEvents($eventStream->events);
        if (!($aggregate instanceof $className)) {
            throw new RuntimeException('Aggregate type mismatch.');
        }
        $this->unitOfWork->persist($aggregate, $eventStream->endVersion);
        $this->snapshotRepository->saveSnapshot(
            $aggregate,
            $this->unitOfWork->getVersion($aggregate)
        );

        return $aggregate;
    }

    private function loadFromSnapshot(AggregateIdInterface $aggregateId, Snapshot $snapshot): Aggregate
    {
        $eventStream = $this->eventStore->loadFromStream($aggregateId, $snapshot->version);
        $aggregate = $snapshot->aggregate;
        foreach ($eventStream->events as $event) {
            $aggregate->reply($event);
        }
        $this->unitOfWork->persist($aggregate, $eventStream->endVersion);

        return $aggregate;
    }
}