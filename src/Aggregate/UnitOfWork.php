<?php

namespace Tcieslar\EventStore\Aggregate;

use InvalidArgumentException;

class UnitOfWork
{
    private array $identityMap = [];

    public function getIdentityMap(): array
    {
        return $this->identityMap;
    }

    public function insert(AggregateInterface $aggregate): void
    {
        $this->throwExceptionIfAggregateAlreadyExists($aggregate, 'Aggregate already exists.');
        $this->identityMap[$aggregate->getId()->toUuidString()] =
            [
                'version' => Version::zero(),
                'aggregate' => $aggregate
            ];
    }

    public function persist(AggregateInterface $aggregate, Version $version): void
    {
        $this->throwExceptionIfAggregateAlreadyExists($aggregate, 'Aggregate already persisted.');
        $this->identityMap[$aggregate->getId()->toUuidString()] =
            [
                'version' => $version,
                'aggregate' => $aggregate
            ];
    }


    public function getVersion(AggregateInterface $aggregate): Version
    {
        $this->throwExceptionIfAggregateNotFound($aggregate);
        return $this->identityMap[$aggregate->getId()->toUuidString()]['version'];
    }

    public function changeVersion(AggregateInterface $aggregate, Version $version): void
    {
        $this->throwExceptionIfAggregateNotFound($aggregate);
        $this->identityMap[$aggregate->getId()->toUuidString()]['version'] = $version;
    }

    public function get(AggregateIdInterface $id): ?AggregateInterface
    {
        if (!isset($this->identityMap[$id->toUuidString()])) {
            return null;
        }

        return $this->identityMap[$id->toUuidString()]['aggregate'];
    }

    public function resetById(AggregateIdInterface $id): void
    {
        unset($this->identityMap[$id->toUuidString()]);
    }

    public function reset(): void
    {
        $this->identityMap = [];
    }

    private function throwExceptionIfAggregateAlreadyExists(AggregateInterface $aggregate, string $message): void
    {
        if (isset($this->identityMap[$aggregate->getId()->toUuidString()])) {
            throw new InvalidArgumentException($message);
        }
    }

    private function throwExceptionIfAggregateNotFound(AggregateInterface $aggregate): void
    {
        if (!isset($this->identityMap[$aggregate->getId()->toUuidString()])) {
            throw new InvalidArgumentException('Aggregate not found.');
        }
    }
}