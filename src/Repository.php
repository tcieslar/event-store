<?php

abstract class Repository
{
    public function __construct(
        private UnitOfWork $unitOfWork
    )
    {
    }

    abstract protected function getClassName(): string;

    protected function getAggregateEvents(IdentityInterface $identity): array
    {
        $eventStream = $this->unitOfWork->loadAggregateEventStream($identity);
        if ($eventStream->isEmpty()) {
            throw new InvalidArgumentException($this->getClassName() . ' not found.');
        }

        return $eventStream->events;
    }

    protected function persistAggregate(Aggregate $aggregate): void
    {
        $this->unitOfWork->persist($aggregate);
    }
}