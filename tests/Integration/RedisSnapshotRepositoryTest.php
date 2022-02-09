<?php

namespace Integration;

use Aggregate\Version;
use Example\Aggregate\Customer;
use Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Snapshot\RedisSnapshotRepository;
use Symfony\Component\Uid\Uuid;
use Utils\PhpSerializer;

/**
 * @group integration
 */
class RedisSnapshotRepositoryTest extends TestCase
{
    public function testSaveAndGet(): void
    {
        $repository = new RedisSnapshotRepository(
            new PhpSerializer()
        );
        $customer = Customer::create(new CustomerId(Uuid::v4()), 'name');
        $repository->saveSnapshot($customer, Version::createVersion(3));
        $customer2 = $repository->getSnapshot($customer->getId());

        $this->assertEquals($customer, $customer2->aggregate);
    }

    public function testNotFound(): void
    {
        $repository = new RedisSnapshotRepository(
            new PhpSerializer()
        );

        $customerId = new CustomerId(Uuid::v4());
        $customer2 = $repository->getSnapshot($customerId);

        $this->assertNull($customer2);
    }
}