<?php

namespace Tcieslar\EventStore\Tests\Functional;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Store\InMemoryEventStore;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Snapshot\InMemorySnapshotRepository;
use Tcieslar\EventStore\Store\InMemoryEventStorage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use Tcieslar\EventStore\Aggregate\Version;

/**
 * @group unit
 */
class InMemorySnapshotRepositoryTest extends TestCase
{
    public function testSave(): void
    {
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $snapshotRepository = new InMemorySnapshotRepository();

        // create aggregate
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $aggregateType = AggregateType::byAggregate($customer);
        $eventStore->appendToStream($customerId, $aggregateType, Version::zero(), $customer->recordedEvents());
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
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $snapshotRepository = new InMemorySnapshotRepository();

        // create aggregate
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $aggregateType = AggregateType::byAggregate($customer);
        $eventStore->appendToStream($customerId, $aggregateType, Version::zero(), $customer->recordedEvents());
        unset($customer);

        // load aggregate
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer2 = Customer::loadFromEvents($eventStream->events);

        // create snapshot
        $snapshotRepository->saveSnapshot($customer2, $eventStream->endVersion);
        $snapshot = $snapshotRepository->getSnapshot($customerId);
        $this->assertSame($snapshot->aggregate->getId(), $customerId);
        $this->assertSame($snapshot->endVersion, $eventStream->endVersion);
    }
}