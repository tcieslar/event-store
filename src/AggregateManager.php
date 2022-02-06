<?php

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

    public function persistAggregate(Aggregate $aggregate, Version $version): void
    {
        $this->unitOfWork->persist($aggregate, $version);
    }

    public function getAggregate(AggregateIdInterface $aggregateId): ?Aggregate
    {
        return $this->unitOfWork->get($aggregateId);
    }

    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot
    {
        return $this->snapshotRepository->getSnapshot($aggregateId);
    }

    public function saveSnapshot(AggregateIdInterface $aggregateId): void
    {
        $aggregate = $this->unitOfWork->get($aggregateId);
        if (!$aggregate) {
            return ;
        }
        $this->snapshotRepository->saveSnapshot(
            $aggregate,
            $this->unitOfWork->getVersion($aggregate)
        );
    }

    public function getEventStream(AggregateIdInterface $id, ?Version $afterVersion = null): EventStream
    {
        return $this->eventStore->loadFromStream($id, $afterVersion);
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
}