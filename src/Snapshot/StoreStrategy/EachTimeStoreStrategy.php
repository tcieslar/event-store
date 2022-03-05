<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot\StoreStrategy;

use Tcieslar\EventStore\Snapshot\Snapshot;
use Tcieslar\EventStore\Snapshot\SnapshotStoreStrategyInterface;

class EachTimeStoreStrategy implements SnapshotStoreStrategyInterface
{
    public function whetherToStoreNew(?Snapshot $lastSnapshot): bool
    {
        return true;
    }
}