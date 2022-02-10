<?php

namespace Snapshot;

use Aggregate\Aggregate;
use Aggregate\AggregateIdInterface;
use Aggregate\Version;
use Utils\SerializerInterface;

class InMemorySnapshotRepository extends AbstractSnapshotRepository
{
    private array $snapshots = [];

    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct($serializer);
    }

    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot
    {
        $idString = $aggregateId->toString();
        return $this->snapshots[$idString] ?? null;
    }

    public function saveSnapshot(Aggregate $aggregate, Version $version): void
    {
        $idString = $aggregate->getId()->toString();

        $this->store($version, $aggregate, $idString);
    }

    private function store(Version $version, Aggregate $aggregate, string $idString): void
    {
        $this->snapshots[$idString] = new Snapshot($aggregate, $version);
    }
}