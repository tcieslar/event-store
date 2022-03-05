<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\Aggregate;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Utils\Uuid;

class InMemorySnapshotRepository implements SnapshotRepositoryInterface
{
    private array $snapshots = [];

    public function __construct()
    {
    }

    public function getSnapshot(Uuid $aggregateId): ?Snapshot
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
        $this->snapshots[$idString] = new Snapshot($aggregate, $version, new \DateTimeImmutable());
    }
}