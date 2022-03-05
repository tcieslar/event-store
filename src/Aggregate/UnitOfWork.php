<?php

namespace Tcieslar\EventStore\Aggregate;

use InvalidArgumentException;
use Tcieslar\EventStore\Utils\Uuid;

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
        $this->identityMap[$aggregate->getUuid()->toString()] =
            [
                'version' => Version::zero(),
                'aggregate' => $aggregate
            ];
    }

    public function persist(AggregateInterface $aggregate, Version $version): void
    {
        $this->throwExceptionIfAggregateAlreadyExists($aggregate, 'Aggregate already persisted.');
        $this->identityMap[$aggregate->getUuid()->toString()] =
            [
                'version' => $version,
                'aggregate' => $aggregate
            ];
    }


    public function getVersion(AggregateInterface $aggregate): Version
    {
        $this->throwExceptionIfAggregateNotFound($aggregate);
        return $this->identityMap[$aggregate->getUuid()->toString()]['version'];
    }

    public function changeVersion(AggregateInterface $aggregate, Version $version): void
    {
        $this->throwExceptionIfAggregateNotFound($aggregate);
        $this->identityMap[$aggregate->getUuid()->toString()]['version'] = $version;
    }

    public function get(Uuid $id): ?AggregateInterface
    {
        if (!isset($this->identityMap[$id->toString()])) {
            return null;
        }

        return $this->identityMap[$id->toString()]['aggregate'];
    }

    public function resetById(Uuid $id): void
    {
        unset($this->identityMap[$id->toString()]);
    }

    public function reset(): void
    {
        $this->identityMap = [];
    }

    private function throwExceptionIfAggregateAlreadyExists(AggregateInterface $aggregate, string $message): void
    {
        if (isset($this->identityMap[$aggregate->getUuid()->toString()])) {
            throw new InvalidArgumentException($message);
        }
    }

    private function throwExceptionIfAggregateNotFound(AggregateInterface $aggregate): void
    {
        if (!isset($this->identityMap[$aggregate->getUuid()->toString()])) {
            throw new InvalidArgumentException('Aggregate not found.');
        }
    }
}