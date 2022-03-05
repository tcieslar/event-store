<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\Aggregate;
use Tcieslar\EventStore\Aggregate\Version;
use Redis;
use Tcieslar\EventStore\Utils\Uuid;

/**
 * @codeCoverageIgnore
 */
class RedisSnapshotRepository implements SnapshotRepositoryInterface
{
    private ?Redis $redis = null;

    public function __construct(
        private string $host,
        private int    $port = 6379
    )
    {
    }

    public function __destruct()
    {
        $this->redis?->close();
    }

    public function getSnapshot(Uuid $aggregateId): ?Snapshot
    {
        if (!$this->redis) {
            $this->connect();
        }
        $key = $this->getKey($aggregateId);
        /** @var array $data */
        $data = $this->redis->hGetAll($key);
        if (!isset($data['o'], $data['t'], $data['v'])) {
            return null;
        }
        $aggregate = unserialize($data["o"], ['allowed_classes' => true]);
        $createdAt = (new \DateTimeImmutable)->setTimestamp((int)$data['t']);

        return new Snapshot($aggregate, Version::number((int)$data['v']), $createdAt);
    }

    public function saveSnapshot(Aggregate $aggregate, Version $version): void
    {
        if (!$this->redis) {
            $this->connect();
        }
        $key = $this->getKey($aggregate->getId());
        $this->redis->hMSet($key, [
            'v' => $version->toString(),
            'o' => serialize($aggregate),
            't' => (new \DateTimeImmutable())->getTimestamp()
        ]);
    }

    private function getKey(Uuid $aggregateId): string
    {
        return 'aggregate-' . $aggregateId->toString();
    }

    private function connect(): void
    {
        $this->redis = new Redis();
        $this->redis->connect(
            $this->host,
            $this->port
        );
    }
}