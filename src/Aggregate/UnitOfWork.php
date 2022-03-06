<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use InvalidArgumentException;
use Tcieslar\EventSourcing\Aggregate;
use Tcieslar\EventSourcing\Uuid;

class UnitOfWork
{
    private array $identityMap = [];

    public function getIdentityMap(): array
    {
        return $this->identityMap;
    }

    public function insert(Aggregate $aggregate): void
    {
        $this->throwExceptionIfAggregateAlreadyExists($aggregate, 'Aggregate already exists.');
        $this->identityMap[$aggregate->getId()->toString()] =
            [
                'version' => Version::zero(),
                'aggregate' => $aggregate
            ];
    }

    public function persist(Aggregate $aggregate, Version $version): void
    {
        $this->throwExceptionIfAggregateAlreadyExists($aggregate, 'Aggregate already persisted.');
        $this->identityMap[$aggregate->getId()->toString()] =
            [
                'version' => $version,
                'aggregate' => $aggregate
            ];
    }


    public function getVersion(Aggregate $aggregate): Version
    {
        $this->throwExceptionIfAggregateNotFound($aggregate);
        return $this->identityMap[$aggregate->getId()->toString()]['version'];
    }

    public function changeVersion(Aggregate $aggregate, Version $version): void
    {
        $this->throwExceptionIfAggregateNotFound($aggregate);
        $this->identityMap[$aggregate->getId()->toString()]['version'] = $version;
    }

    public function get(Uuid $id): ?Aggregate
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

    private function throwExceptionIfAggregateAlreadyExists(Aggregate $aggregate, string $message): void
    {
        if (isset($this->identityMap[$aggregate->getId()->toString()])) {
            throw new InvalidArgumentException($message);
        }
    }

    private function throwExceptionIfAggregateNotFound(Aggregate $aggregate): void
    {
        if (!isset($this->identityMap[$aggregate->getId()->toString()])) {
            throw new InvalidArgumentException('Aggregate not found.');
        }
    }
}