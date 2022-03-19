<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use Tcieslar\EventSourcing\Aggregate;
use Tcieslar\EventStore\Exception\AggregateNotFoundException;
use Tcieslar\EventSourcing\Uuid;

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

    public function addAggregate(Aggregate $aggregate): void
    {
        $this->aggregateManager->addAggregate($aggregate);
    }

    public function saveAggregate(Aggregate $aggregate): void
    {
        $this->aggregateManager->flushAggregate($aggregate);
    }
}