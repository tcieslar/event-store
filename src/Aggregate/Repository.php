<?php

namespace Tcieslar\EventStore\Aggregate;

use Tcieslar\EventStore\Exception\AggregateNotFoundException;
use Tcieslar\EventStore\Utils\Uuid;

abstract class Repository
{
    public function __construct(
        protected AggregateManager $aggregateManager
    )
    {
    }

    /**
     * @throws AggregateNotFoundException
     */
    public function findOne(Uuid $aggregateId)
    {
        return $this->aggregateManager->findAggregate($aggregateId);
    }

    public function add(Aggregate $aggregate): void
    {
        $this->aggregateManager->addAggregate($aggregate);
    }
}