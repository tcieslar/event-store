<?php

class UnitOfWork
{
    private array $identityMap = [];

    public function getIdentityMap(): array
    {
        return $this->identityMap;
    }

    public function insert(Aggregate $aggregate): void
    {
        if (isset($this->identityMap[$aggregate->getId()->toString()])) {
            throw new InvalidArgumentException('Aggregate already exists.');
        }
        $this->identityMap[$aggregate->getId()->toString()] =
            [
                'version' => Version::createZeroVersion(),
                'aggregate' => $aggregate
            ];
    }

    public function persist(Aggregate $aggregate, Version $version): void
    {
        if (isset($this->identityMap[$aggregate->getId()->toString()])) {
            throw new InvalidArgumentException('Aggregate already persisted.');
        }
        $this->identityMap[$aggregate->getId()->toString()] =
            [
                'version' => $version,
                'aggregate' => $aggregate
            ];
    }

    public function changeVersion(Aggregate $aggregate, Version $version): void
    {
        if (!isset($this->identityMap[$aggregate->getId()->toString()])) {
            throw new InvalidArgumentException('Aggregate not found.');
        }
        $this->identityMap[$aggregate->getId()->toString()]['version'] = $version;
    }

    public function get(AggregateIdInterface $id): ?Aggregate
    {
        if (!isset($this->identityMap[$id->toString()])) {
            return null;
        }

        return $this->identityMap[$id->toString()]['aggregate'];
    }

    public function reset(): void
    {
        $this->identityMap = [];
    }
}