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
        if (!$snapshot) {
            $this->logger->debug("Cannot find aggregate ({$aggregateId->toString()}) snapshot.", [
                'aggregate_id' => $aggregateId->toString(),
                'version' => (int)$snapshot?->endVersion->toString(),
                'created_at' => $snapshot?->createdAt
            ]);

            return null;
        }
        $version = (int)$snapshot->endVersion->toString();
        $this->logger->debug("Aggregate {$aggregateId->toString()} snapshot loaded. Loaded version {$version}.", [
            'aggregate_id' => $aggregateId->toString(),
            'version' => $version,
            'created_at' => $snapshot->createdAt
        ]);

        return $snapshot;
    }

    public function saveSnapshot(Aggregate $aggregate, Version $version): void
    {
        $this->snapshotRepository->saveSnapshot($aggregate, $version);
        $versionString = $version->toString();
        $this->logger->debug("Aggregate {$aggregate->getId()->toString()} snapshot saved. Saved version {$versionString}.", [
            'aggregate_id' => $aggregate->getId()->toString(),
            'version' => $versionString
        ]);
    }
}