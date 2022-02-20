<?php

namespace Tcieslar\EventStore\Tests\Integration;

use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Snapshot\RedisSnapshotRepository;
use Symfony\Component\Uid\Uuid;

/**
 * @group integration
 */
class RedisSnapshotRepositoryTest extends TestCase
{
    public function testSaveAndGet(): void
    {
        $repository = new RedisSnapshotRepository();
        $customer = Customer::create(new CustomerId(Uuid::v4()), 'name');
        $repository->saveSnapshot($customer, Version::number(3));
        $customer2 = $repository->getSnapshot($customer->getId());

        $this->assertEquals($customer, $customer2->aggregate);
    }

    public function testNotFound(): void
    {
        $repository = new RedisSnapshotRepository();

        $customerId = new CustomerId(Uuid::v4());
        $customer2 = $repository->getSnapshot($customerId);

        $this->assertNull($customer2);
    }
}