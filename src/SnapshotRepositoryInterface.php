<?php

interface SnapshotRepositoryInterface
{
    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot;

    public function saveSnapshot(Aggregate $aggregate, Version $version): void;
}