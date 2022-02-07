<?php

namespace Snapshot;

use Aggregate\Aggregate;
use Aggregate\AggregateIdInterface;
use Aggregate\Version;

interface SnapshotRepositoryInterface
{
    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot;

    public function saveSnapshot(Aggregate $aggregate, Version $version): void;
}