<?php declare(strict_types=1);

namespace Tcieslar\EventStore;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateInterface;

interface AggregateManagerInterface
{
    public function addAggregate(AggregateInterface $aggregate): void;

    public function findAggregate(AggregateIdInterface $aggregateId): mixed;

    public function reset(): void;

    public function flush(): void;
}