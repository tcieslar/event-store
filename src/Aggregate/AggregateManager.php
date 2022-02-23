<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use Tcieslar\EventStore\AggregateManagerInterface;
use Tcieslar\EventStore\Exception\AggregateNotFoundException;
use Tcieslar\EventStore\Exception\AggregateReloadNeedException;
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

    /**
     * @throws AggregateNotFoundException
     */
    public function findAggregate(AggregateIdInterface $aggregateId): ?AggregateInterface
    {
        if ($aggregate = $this->loadFromMemory($aggregateId)) {
            return $aggregate;
        }

        if ($aggregate = $this->loadFromSnapshot($aggregateId)) {
            return $aggregate;
        }

        return $this->loadFromStore($aggregateId);
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
        $aggregatesIdsToReload = [];
        foreach ($identityMap as $row) {
            /** @var AggregateInterface $aggregate */
            $aggregate = $row['aggregate'];
            /** @var Version $currentVersion */
            $currentVersion = $row['version'];

            try {
                $newVersion = $this->eventStore->appendToStream($aggregate->getId(), $aggregate->getType(), $currentVersion, $aggregate->recordedEvents());
                $this->unitOfWork->changeVersion($aggregate, $newVersion);
                $aggregate->removeRecordedEvents();
            } catch (ConcurrencyException $exception) {
                $this->unitOfWork->resetById($aggregate->getId());
                $this->concurrencyResolvingStrategy->resolve($exception);
                $aggregatesIdsToReload[] = $aggregate->getId();
            }
        }

        if (!empty($aggregatesIdsToReload)) {
            throw new AggregateReloadNeedException($aggregatesIdsToReload);
        }
    }

    private function loadFromMemory(AggregateIdInterface $aggregateId): ?AggregateInterface
    {
        return $this->unitOfWork->get($aggregateId);
    }

    /**
     * @throws AggregateNotFoundException
     */
    private function loadFromStore(AggregateIdInterface $aggregateId): AggregateInterface
    {
        $eventStream = $this->eventStore->loadFromStream($aggregateId);
        $classFqcn = $eventStream->aggregateType->toString();
        $aggregate = $classFqcn::loadFromEvents($eventStream->events);
        $this->unitOfWork->persist($aggregate, $eventStream->endVersion);
        $this->snapshotRepository->saveSnapshot(
            $aggregate,
            $eventStream->endVersion
        );

        return $aggregate;
    }

    private function loadFromSnapshot(AggregateIdInterface $aggregateId): ?AggregateInterface
    {
        $snapshot = $this->snapshotRepository->getSnapshot($aggregateId);
        if (!$snapshot) {
            return null;
        }

        $eventStream = $this->eventStore->loadFromStream($aggregateId, $snapshot->endVersion);
        $aggregate = $snapshot->aggregate;
        foreach ($eventStream->events as $event) {
            $aggregate->reply($event);
        }
        $this->unitOfWork->persist($aggregate, $eventStream->endVersion);

        return $aggregate;
    }
}