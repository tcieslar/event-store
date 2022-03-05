<?php declare(strict_types=1);

namespace Tcieslar\EventStore;

use Tcieslar\EventStore\Aggregate\AggregateInterface;
use Tcieslar\EventStore\Utils\Uuid;

interface AggregateManagerInterface
{
    public function addAggregate(AggregateInterface $aggregate): void;

    public function findAggregate(Uuid $aggregateId): mixed;

    public function reset(): void;

    public function flush(): void;
}