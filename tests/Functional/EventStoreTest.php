<?php

namespace Tcieslar\EventStore\Tests\Functional;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Store\InMemoryEventStore;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Store\InMemoryEventStorage;
use PHPUnit\Framework\TestCase;

use Tcieslar\EventStore\Aggregate\Version;

/**
 * @group unit
 */
class EventStoreTest extends TestCase
{
    public function testLoadEmpty(): void
    {
        $customerId = CustomerId::create();
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );

        $this->expectExceptionMessage('Aggregate not found.');
        $eventStore->loadFromStream($customerId->getUuid());
    }

    public function testNewAggregate(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');

        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $aggregateType = AggregateType::byAggregate($customer);
        $eventStore->appendToStream($customerId->getUuid(), $aggregateType, Version::zero(), $customer->recordedEvents());

        $eventStream = $eventStore->loadFromStream($customerId->getUuid());
        $this->assertCount(2, $eventStream->events);
        $this->assertEquals('0', $eventStream->startVersion->toString());
        $this->assertEquals('2', $eventStream->endVersion->toString());
    }

    public function testUpdateAggregate(): void
    {
        // create
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $aggregateType = AggregateType::byAggregate($customer);
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );

        $this->assertEquals('test', $customer->getName());
        $event1 = $customer->recordedEvents()->get(0);

        // insert
        $eventStore->appendToStream($customerId->getUuid(), $aggregateType, Version::zero(), $customer->recordedEvents());

        // read
        $eventStream = $eventStore->loadFromStream($customerId->getUuid());
        $this->assertEquals($eventStream->events->get(0), $event1);

        // insert 2
        $customer = Customer::loadFromEvents($eventStream->events);
        $customer->setName('test2');
        $eventStore->appendToStream($customerId->getUuid(), $aggregateType, $eventStream->endVersion, $customer->recordedEvents());

        //read 2
        $eventStream = $eventStore->loadFromStream($customerId->getUuid());
        $customer = Customer::loadFromEvents($eventStream->events);

        $this->assertEquals('test2', $customer->getName());
        $this->assertEquals('3', $eventStream->endVersion->toString());
//        $this->assertCount(3, $eventStream->events);
    }

    public function testLoadFromStream(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $aggregateType = AggregateType::byAggregate($customer);
        $customer->setName('test 2');
        $customer->setName('test 3');
        $customer->setName('test 4');

        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $eventStore->appendToStream($customerId->getUuid(), $aggregateType, Version::zero(), $customer->recordedEvents());

        $eventStream = $eventStore->loadFromStream($customerId->getUuid(), Version::zero());
        $this->assertCount(5, $eventStream->events);
        $eventStream = $eventStore->loadFromStream($customerId->getUuid(), Version::number(1));
        $this->assertCount(4, $eventStream->events);
        $eventStream = $eventStore->loadFromStream($customerId->getUuid(), Version::number(2));
        $this->assertCount(3, $eventStream->events);
        $eventStream = $eventStore->loadFromStream($customerId->getUuid(), Version::number(4));
        $this->assertCount(1, $eventStream->events);
    }
}