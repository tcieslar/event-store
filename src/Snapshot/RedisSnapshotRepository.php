<?php

namespace Snapshot;

use Aggregate\Aggregate;
use Aggregate\AggregateIdInterface;
use Aggregate\Version;

class RedisSnapshotRepository implements SnapshotRepositoryInterface
{
    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot
    {
        // TODO: Implement getSnapshot() method.
    }

    public function saveSnapshot(Aggregate $aggregate, Version $version): void
    {
        // TODO: Implement saveSnapshot() method.
    }
}