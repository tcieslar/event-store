<?php

namespace Integration;

use Aggregate\Version;
use Example\Aggregate\Customer;
use Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Snapshot\RedisSnapshotRepository;
use Symfony\Component\Uid\Uuid;
use Utils\PhpSerializer;

class RedisSnapshotRepositoryTest extends TestCase
{
    public function testSaveAndGet(): void
    {
        $repo = new RedisSnapshotRepository(
            new PhpSerializer()
        );
        $customer = Customer::create(new CustomerId(Uuid::v4()), 'name');
        $repo->saveSnapshot($customer, Version::createVersion(3));
        $customer2 = $repo->getSnapshot($customer->getId());

        $this->assertEquals($customer, $customer2->aggregate);
    }

    public function testNotFound(): void
    {
        $repo = new RedisSnapshotRepository(
            new PhpSerializer()
        );

        $customerId = new CustomerId(Uuid::v4());
        $customer2 = $repo->getSnapshot($customerId);

        $this->assertNull($customer2);
    }
}