<?php

class UnitOfWork
{
    private array $identityMap = [];
    /**
     * @param array<Aggregate> $identityMap
     */
    public function __construct(
        private EventStoreInterface $eventStore,
    )
    {
    }

    public function loadAggregateEventStream(IdentityInterface $identity): EventStream
    {
        return $this->eventStore->loadEventStream($identity);
    }

    public function persist(Aggregate $aggregate): void
    {
        $this->identityMap[$aggregate->getId()->toString()] = $aggregate;
    }

    public function reset(): void
    {
        $this->identityMap = [];
    }

    public function flush(): void
    {
        foreach ($this->identityMap as $aggregate) {
            $this->eventStore->appendToStream($aggregate->getId(), 1, $aggregate->getChanges());
        }
    }
}