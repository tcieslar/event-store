<?php

abstract class Repository
{
    public function __construct(
        private UnitOfWork $unitOfWork
    )
    {
    }

    public function add(Aggregate $aggregate): void
    {
        $this->unitOfWork->insert($aggregate);
    }

    public function findAggregate(AggregateIdInterface $id): ?Aggregate
    {
        if ($aggregate = $this->unitOfWork->get($id)) {
            return $aggregate;
        }

        $eventStream = $this->unitOfWork->loadAggregateEventStream($id);
        $aggregate = $this->createAggregateByEventStream($eventStream);
        $this->unitOfWork->persist($aggregate, $eventStream->version);

        return $aggregate;
    }

    abstract protected function createAggregateByEventStream(EventStream $eventStream): Aggregate;
}