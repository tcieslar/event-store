<?php

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateInterface;
use Tcieslar\EventStore\Aggregate\Version;

class InMemorySnapshotRepository implements SnapshotRepositoryInterface
{
    private array $snapshots = [];

    public function __construct()
    {
    }

    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot
    {
        $idString = $aggregateId->toString();
        return $this->snapshots[$idString] ?? null;
    }

    public function saveSnapshot(AggregateInterface $aggregate, Version $version): void
    {
        $idString = $aggregate->getId()->toString();

        $this->store($version, $aggregate, $idString);
    }

    private function store(Version $version, AggregateInterface $aggregate, string $idString): void
    {
        $this->snapshots[$idString] = new Snapshot($aggregate, $version, new \DateTimeImmutable());
    }
}