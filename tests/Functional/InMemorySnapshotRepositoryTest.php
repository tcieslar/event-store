<?php

namespace Functional;

use Example\Customer;
use Example\CustomerId;
use Example\EventStore;
use InMemorySnapshotRepository;
use InMemoryStorage;
use PhpSerializer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Uid\Uuid;
use Version;

/**
 * @group unit
 */
class InMemorySnapshotRepositoryTest extends TestCase
{
    public function testSave(): void
    {
        $eventStore = new EventStore(new InMemoryStorage());
        $serializer = new PhpSerializer();
        $snapshotRepository = new InMemorySnapshotRepository($serializer);

        // create aggregate
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventStore->appendToStream($customerId, Version::createFirstVersion(), $customer->getChanges());
        unset($customer);

        // load aggregate
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer2 = Customer::loadFromEvents($eventStream->events);

        // create snapshot
        $snapshotRepository->saveSnapshot($customer2, $eventStream->endVersion);

        // get private snapshots array
        $reflectionClass = new ReflectionClass('InMemorySnapshotRepository');
        $reflectionProperty = $reflectionClass->getProperty('snapshots');
        $snapshots = $reflectionProperty->getValue($snapshotRepository);

        $this->assertEquals($customerId, current($snapshots)->aggregate->getId());
    }

    public function testGet(): void
    {
        $eventStore = new EventStore(new InMemoryStorage());
        $serializer = new PhpSerializer();
        $snapshotRepository = new InMemorySnapshotRepository($serializer);

        // create aggregate
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventStore->appendToStream($customerId, Version::createFirstVersion(), $customer->getChanges());
        unset($customer);

        // load aggregate
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer2 = Customer::loadFromEvents($eventStream->events);

        // create snapshot
        $snapshotRepository->saveSnapshot($customer2, $eventStream->endVersion);

        $snapshot = $snapshotRepository->getSnapshot($customerId);
        $this->assertSame($snapshot->aggregate->getId(), $customerId);
        $this->assertSame($snapshot->version, $eventStream->endVersion);
    }
}