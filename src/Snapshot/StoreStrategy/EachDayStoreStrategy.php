<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot\StoreStrategy;

use Tcieslar\EventStore\Snapshot\Snapshot;
use Tcieslar\EventStore\Snapshot\SnapshotStoreStrategyInterface;

class EachDayStoreStrategy implements SnapshotStoreStrategyInterface
{
    public function whetherToStoreNew(?Snapshot $lastSnapshot): bool
    {
        $today = (new \DateTimeImmutable())->modify('today midnight');
        return $lastSnapshot?->createdAt < $today ;
    }
}