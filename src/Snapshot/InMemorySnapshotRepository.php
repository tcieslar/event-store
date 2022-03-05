<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateInterface;
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

    public function saveSnapshot(AggregateInterface $aggregate, Version $version): void
    {
        $idString = $aggregate->getUuid()->toString();

        $this->store($version, $aggregate, $idString);
    }

    private function store(Version $version, AggregateInterface $aggregate, string $idString): void
    {
        $this->snapshots[$idString] = new Snapshot($aggregate, $version, new \DateTimeImmutable());
    }
}