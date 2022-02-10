<?php

namespace Snapshot;

use Aggregate\AggregateIdInterface;
use Aggregate\AggregateInterface;
use Aggregate\Version;

interface SnapshotRepositoryInterface
{
    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot;

    public function saveSnapshot(AggregateInterface $aggregate, Version $version): void;
}