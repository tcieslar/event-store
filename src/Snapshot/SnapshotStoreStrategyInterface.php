<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot;

interface SnapshotStoreStrategyInterface
{
    public function whetherToStoreNew(?Snapshot $lastSnapshot): bool;
}