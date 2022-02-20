<?php

namespace Tcieslar\EventStore\Aggregate;

abstract class Repository
{
    public function __construct(
        protected AggregateManager $aggregateManager
    )
    {
    }

    public function findOne(AggregateIdInterface $aggregateId)
    {
        return $this->aggregateManager->findAggregate($aggregateId);
    }

    public function add(AggregateInterface $aggregate): void
    {
        $this->aggregateManager->addAggregate($aggregate);
    }
}