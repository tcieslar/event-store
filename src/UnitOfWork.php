<?php

class UnitOfWork
{
    private array $identityMap = [];


    public function __construct(
        private EventStoreInterface $eventStore,
    )
    {
    }

    public function insert(Aggregate $aggregate): void
    {
        if (isset($this->identityMap[$aggregate->getId()->toString()])) {
            throw new InvalidArgumentException('Aggregate already exists.');
        }

        $this->identityMap[$aggregate->getId()->toString()] =
            [
                'version' => Version::createFirstVersion(),
                'aggregate' => $aggregate
            ];
    }

    public function get(AggregateIdInterface $id): ?Aggregate
    {
        if (!isset($this->identityMap[$id->toString()])) {
            return null;
        }

        return $this->identityMap[$id->toString()]['aggregate'];
    }

    public function persist(Aggregate $aggregate, EventStream $eventStream): void
    {
        if (isset($this->identityMap[$aggregate->getId()->toString()])) {
            throw new InvalidArgumentException('Aggregate already persisted.');
        }

        $this->identityMap[$aggregate->getId()->toString()] =
            [
                'version' => $eventStream->version,
                'aggregate' => $aggregate
            ];

    }

    public function reset(): void
    {
        $this->identityMap = [];
    }

    public function flush(): void
    {
        foreach ($this->identityMap as $row) {
            $aggregate = $row['aggregate'];
            $version = $row['version'];
            $this->eventStore->appendToStream($aggregate->getId(), $version, $aggregate->getChanges());
        }
    }

    public function loadAggregateEventStream(AggregateIdInterface $identity): EventStream
    {
        return $this->eventStore->loadEventStream($identity);
    }
}