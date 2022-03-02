<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateInterface;
use Tcieslar\EventStore\Aggregate\Version;

interface SnapshotRepositoryInterface
{
    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot;

    public function saveSnapshot(AggregateInterface $aggregate, Version $version): void;
}