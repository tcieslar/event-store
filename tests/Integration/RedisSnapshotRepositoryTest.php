<?php

namespace Integration;

use PHPUnit\Framework\TestCase;
use Redis;

class RedisSnapshotRepositoryTest extends TestCase
{
    public function testOne(): void
    {
        $redis = new Redis();
        $redis->connect(
            '127.0.0.1'
        );

    }
}