<?php

namespace Tcieslar\EventStore\Tests\Integration;

use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Snapshot\RedisSnapshotRepository;


/**
 * @group integration
 */
class RedisSnapshotRepositoryTest extends TestCase
{
    private static $redisHost = '127.0.0.1';

    public function testSaveAndGet(): void
    {
        $repository = new RedisSnapshotRepository(self::$redisHost);
        $customer = Customer::create(CustomerId::create(), 'name');
        $repository->saveSnapshot($customer, Version::number(3));
        $snapshot = $repository->getSnapshot($customer->getId());

        $this->assertEquals($customer, $snapshot->aggregate);
        $this->assertInstanceOf(\DateTimeImmutable::class, $snapshot->createdAt);
    }

    public function testNotFound(): void
    {
        $repository = new RedisSnapshotRepository(self::$redisHost);

        $customerId = CustomerId::create();
        $customer2 = $repository->getSnapshot($customerId);

        $this->assertNull($customer2);
    }
}