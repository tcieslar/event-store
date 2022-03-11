<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventSourcing\Aggregate;use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventSourcing\Uuid;

interface SnapshotRepositoryInterface
{
    public function getSnapshot(Uuid $aggregateId): ?Snapshot;

    public function saveSnapshot(Aggregate $aggregate, Version $version): void;
}