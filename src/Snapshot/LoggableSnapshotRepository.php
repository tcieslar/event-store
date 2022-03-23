<?php

namespace Tcieslar\EventStore\Snapshot;

use Psr\Log\LoggerInterface;
use Tcieslar\EventSourcing\Aggregate;
use Tcieslar\EventSourcing\Uuid;
use Tcieslar\EventStore\Aggregate\Version;

class LoggableSnapshotRepository implements SnapshotRepositoryInterface
{
    private SnapshotRepositoryInterface $snapshotRepository;
    private LoggerInterface $logger;

    public function __construct(SnapshotRepositoryInterface $snapshotRepository, LoggerInterface $logger)
    {
        $this->snapshotRepository = $snapshotRepository;
        $this->logger = $logger;
    }

    public function getSnapshot(Uuid $aggregateId): ?Snapshot
    {
        $snapshot = $this->snapshotRepository->getSnapshot($aggregateId);
        $this->logger->debug('Load aggregate snapshot.', [
            'aggregate_id' => $aggregateId->toString(),
            'version' => (int)$snapshot?->endVersion->toString(),
            'created_at' => $snapshot?->createdAt
        ]);

        return $snapshot;
    }

    public function saveSnapshot(Aggregate $aggregate, Version $version): void
    {
        $this->snapshotRepository->saveSnapshot($aggregate, $version);
        $this->logger->debug('Save aggregate snapshot.', [
            'aggregate_id' => $aggregate->getId()->toString(),
            'version' => (int)$version->toString()
        ]);
    }
}