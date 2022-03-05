<?php

namespace Tcieslar\EventStore\Aggregate;

use Tcieslar\EventStore\Utils\Uuid;

abstract class Repository
{
    public function __construct(
        protected AggregateManager $aggregateManager
    )
    {
    }

    public function findOne(Uuid $aggregateId)
    {
        return $this->aggregateManager->findAggregate($aggregateId);
    }

    public function add(AggregateInterface $aggregate): void
    {
        $this->aggregateManager->addAggregate($aggregate);
    }
}