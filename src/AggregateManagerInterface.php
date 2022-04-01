<?php declare(strict_types=1);

namespace Tcieslar\EventStore;

use Tcieslar\EventSourcing\Aggregate;
use Tcieslar\EventSourcing\Uuid;

interface AggregateManagerInterface
{
    public function addAggregate(Aggregate $aggregate): void;

    public function findAggregate(Uuid $aggregateId): mixed;

    public function reset(): void;

    public function flush(): void;

    /**
     * @return bool - is there a need to reload aggregate
     */
    public function flushAggregate(Aggregate $paramAggregate): bool;
}