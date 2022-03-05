<?php declare(strict_types=1);

namespace Tcieslar\EventStore;

use Tcieslar\EventStore\Aggregate\Aggregate;
use Tcieslar\EventStore\Utils\Uuid;

interface AggregateManagerInterface
{
    public function addAggregate(Aggregate $aggregate): void;

    public function findAggregate(Uuid $aggregateId): mixed;

    public function reset(): void;

    public function flush(): void;
}