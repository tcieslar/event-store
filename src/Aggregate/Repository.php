<?php


namespace Aggregate;

abstract class Repository
{
    public function __construct(
        protected AggregateManager $aggregateManager
    )
    {
    }

    public function findOne(AggregateIdInterface $aggregateId)
    {
        return $this->aggregateManager->findAggregate(static::getAggregateClassName(), $aggregateId);
    }

    public function add(AggregateInterface $aggregate): void
    {
        $this->aggregateManager->addAggregate($aggregate);
    }

    abstract protected static function getAggregateClassName(): string;
}