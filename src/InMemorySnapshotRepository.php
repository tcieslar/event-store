<?php

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
        if (!isset($this->snapshots[$idString])) {
            return null;
        }

        return $this->snapshots[$idString];
    }

    public function saveSnapshot(Aggregate $aggregate, Version $version): void
    {
        $idString = $aggregate->getId()->toString();
        if (!isset($this->snapshots[$idString])) {
            $this->store($version, $aggregate, $idString);
            return;
        }

        /** @var Version $currentVersion */
        $currentVersion = $this->snapshots[$idString]->version;
        if ($currentVersion->isHigherThen($version)) {
            return;
        }

        $this->store($version, $aggregate, $idString);
    }

    private function store(Version $version, Aggregate $aggregate, string $idString): void
    {
        $this->snapshots[$idString] = new Snapshot($aggregate, $version);
    }
}