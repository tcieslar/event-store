<?php

namespace Tcieslar\EventStore\Tests\Functional;

use Tcieslar\EventStore\EventStore;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Snapshot\InMemorySnapshotRepository;
use Tcieslar\EventStore\Storage\InMemoryEventStorage;
use Tcieslar\EventStore\Utils\PhpSerializer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\Version;

/**
 * @group unit
 */
class InMemorySnapshotRepositoryTest extends TestCase
{
    public function testSave(): void
    {
        $eventStore = new EventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $serializer = new PhpSerializer();
        $snapshotRepository = new InMemorySnapshotRepository($serializer);

        // create aggregate
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventStore->appendToStream($customerId, Version::createZeroVersion(), $customer->recordedEvents());
        unset($customer);

        // load aggregate
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer2 = Customer::loadFromEvents($eventStream->events);

        // create snapshot
        $snapshotRepository->saveSnapshot($customer2, $eventStream->endVersion);

        // get private snapshots array
        $reflectionClass = new ReflectionClass(InMemorySnapshotRepository::class);
        $reflectionProperty = $reflectionClass->getProperty('snapshots');
        $snapshots = $reflectionProperty->getValue($snapshotRepository);

        $this->assertEquals($customerId, current($snapshots)->aggregate->getId());
    }

    public function testGet(): void
    {
        $eventStore = new EventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $serializer = new PhpSerializer();
        $snapshotRepository = new InMemorySnapshotRepository($serializer);

        // create aggregate
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventStore->appendToStream($customerId, Version::createZeroVersion(), $customer->recordedEvents());
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