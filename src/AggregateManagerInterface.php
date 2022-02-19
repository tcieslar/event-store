<?php

namespace Tcieslar\EventStore;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateInterface;

interface AggregateManagerInterface
{
    public function addAggregate(AggregateInterface $aggregate): void;

    public function findAggregate(string $className, AggregateIdInterface $aggregateId);

    public function reset(): void;

    public function flush(): void;
}