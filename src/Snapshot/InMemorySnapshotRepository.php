<?php

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateInterface;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Utils\SerializerInterface;

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

    public function saveSnapshot(AggregateInterface $aggregate, Version $version): void
    {
        $idString = $aggregate->getId()->toString();

        $this->store($version, $aggregate, $idString);
    }

    private function store(Version $version, AggregateInterface $aggregate, string $idString): void
    {
        $this->snapshots[$idString] = new Snapshot($aggregate, $version);
    }
}