<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateInterface;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Utils\Uuid;

interface SnapshotRepositoryInterface
{
    public function getSnapshot(Uuid $aggregateId): ?Snapshot;

    public function saveSnapshot(AggregateInterface $aggregate, Version $version): void;
}