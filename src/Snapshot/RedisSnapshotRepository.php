<?php

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateInterface;
use Tcieslar\EventStore\Aggregate\Version;
use Redis;

/**
 * @codeCoverageIgnore
 */
class RedisSnapshotRepository implements SnapshotRepositoryInterface
{
    private Redis $redis;

    public function __construct()
    {
        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function getSnapshot(AggregateIdInterface $aggregateId): ?Snapshot
    {
        $key = $this->getKey($aggregateId);
        $array = $this->redis->hGetAll($key);
        if (empty($array)) {
            return null;
        }
        $aggregate = unserialize($array['o']);

        return new Snapshot($aggregate, Version::number((int)$array['v']));
    }

    public function saveSnapshot(AggregateInterface $aggregate, Version $version): void
    {
        $key = $this->getKey($aggregate->getId());
        $this->redis->hMSet($key, [
            'v' => $version->toString(),
            'o' => serialize($aggregate)
        ]);
    }

    private function getKey(AggregateIdInterface $aggregateId): string
    {
        return 'aggregate-' . $aggregateId->toString();
    }

    private function connect(): void
    {
        $this->redis = new Redis();
        $this->redis->connect(
            '127.0.0.1'
        );
    }

    private function disconnect(): void
    {
        $this->redis->close();
    }
}