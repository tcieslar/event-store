<?php

class AggregateManager
{
    public function __construct(
        private UnitOfWork $unitOfWork,
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

    public function getEventStream(AggregateIdInterface $id, ?Version $afterVersion = null): EventStream
    {
        return $this->eventStore->loadFromStream($id, $afterVersion);
    }

    public function flush(): void
    {
        foreach ($this->unitOfWork->getIdentityMap() as $row) {
            $aggregate = $row['aggregate'];
            $version = $row['version'];
            try {
                $this->eventStore->appendToStream($aggregate->getId(), $version, $aggregate->getChanges());
            } catch (ConcurrencyException $exception) {
                $this->concurrencyResolvingStrategy->resolve($exception);
            }
        }
    }
}