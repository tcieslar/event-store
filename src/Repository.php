<?php

abstract class Repository
{
    public function __construct(
        private AggregateManager $aggregateManager
    )
    {
    }

    public function add(Aggregate $aggregate): void
    {
        $this->aggregateManager->addAggregate($aggregate);
    }

    public function findAggregate(AggregateIdInterface $aggregateId)
    {
        if ($aggregate = $this->aggregateManager->getAggregate($aggregateId)) {
            return $aggregate;
        }

        $snapshot = $this->aggregateManager->getSnapshot($aggregateId);
        if (!$snapshot) {
            $eventStream = $this->aggregateManager->getEventStream($aggregateId);
            $aggregate = $this->createAggregateByEventStream($eventStream);
            $this->aggregateManager->persistAggregate($aggregate, $eventStream->endVersion);
            return $aggregate;
        }

        $eventStream = $this->aggregateManager->getEventStream($aggregateId, $snapshot->version);
        $aggregate = $snapshot->aggregate;
        foreach ($eventStream->events as $event) {
            $aggregate->reply($event);
        }
        return $aggregate;
    }

    abstract protected function createAggregateByEventStream(EventStream $eventStream): Aggregate;
}