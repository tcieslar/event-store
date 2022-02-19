<?php
declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use RuntimeException;
use Tcieslar\EventStore\AggregateManagerInterface;
use Tcieslar\EventStore\Exception\AggregateReloadNeedException;
use Tcieslar\EventStore\Snapshot\Snapshot;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventStore\ConcurrencyResolving\ConcurrencyResolvingStrategyInterface;
use Tcieslar\EventStore\EventStoreInterface;
use Tcieslar\EventStore\Snapshot\SnapshotRepositoryInterface;

class AggregateManager implements AggregateManagerInterface
{
    public function __construct(
        private UnitOfWork                            $unitOfWork,
        private EventStoreInterface                   $eventStore,
        private SnapshotRepositoryInterface           $snapshotRepository,
        private ConcurrencyResolvingStrategyInterface $concurrencyResolvingStrategy
    )
    {
    }

    public function addAggregate(AggregateInterface $aggregate): void
    {
        $this->unitOfWork->insert($aggregate);
    }

    public function findAggregate(string $className, AggregateIdInterface $aggregateId)
    {
        if ($aggregate = $this->unitOfWork->get($aggregateId)) {
            return $aggregate;
        }

        $snapshot = $this->snapshotRepository->getSnapshot($aggregateId);
        if (!$snapshot) {
            return $this->loadFromStore($aggregateId, $className);
        }

        return $this->loadFromSnapshot($aggregateId, $snapshot);
    }

    public function reset(): void
    {
        $this->unitOfWork->reset();
    }

    /**
     * @throws AggregateReloadNeedException
     */
    public function flush(): void
    {
        $identityMap = $this->unitOfWork->getIdentityMap();
        foreach ($identityMap as $row) {
            /** @var AggregateInterface $aggregate */
            $aggregate = $row['aggregate'];
            /** @var Version $currentVersion */
            $currentVersion = $row['version'];
            try {
                $newVersion = $this->eventStore->appendToStream($aggregate->getId(), $currentVersion, $aggregate->recordedEvents());
                $this->unitOfWork->changeVersion($aggregate, $newVersion);
                $aggregate->removeRecordedEvents();
            } catch (ConcurrencyException $exception) {
                $this->unitOfWork->resetById($aggregate->getId());
                $this->concurrencyResolvingStrategy->resolve($exception);

                throw new AggregateReloadNeedException($aggregate->getId());
            }
        }
    }

    private function loadFromStore(AggregateIdInterface $aggregateId, string $className): mixed
    {
        $eventStream = $this->eventStore->loadFromStream($aggregateId);
        $aggregate = $className::loadFromEvents($eventStream->events);
        $this->unitOfWork->persist($aggregate, $eventStream->endVersion);
        $this->snapshotRepository->saveSnapshot(
            $aggregate,
            $eventStream->endVersion
        );

        return $aggregate;
    }

    private function loadFromSnapshot(AggregateIdInterface $aggregateId, Snapshot $snapshot): AggregateInterface
    {
        $eventStream = $this->eventStore->loadFromStream($aggregateId, $snapshot->version);
        $aggregate = $snapshot->aggregate;
        foreach ($eventStream->events as $event) {
            $aggregate->reply($event);
        }
        $this->unitOfWork->persist($aggregate, $eventStream->endVersion);

        return $aggregate;
    }
}