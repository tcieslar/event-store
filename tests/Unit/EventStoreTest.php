<?php

namespace Unit;

use Example\Customer;
use Example\CustomerId;
use Example\EventStore;
use InMemoryStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Version;

class EventStoreTest extends TestCase
{
    public function testLoadEmpty(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $eventStore = new EventStore(new InMemoryStorage());

        $this->expectExceptionMessage('Aggregate not found.');
        $eventStore->loadFromStream($customerId);
    }

    public function testNewAggregate(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');

        $eventStore = new EventStore(new InMemoryStorage());
        $eventStore->appendToStream($customerId, Version::createFirstVersion(), $customer->getChanges());

        $eventStream = $eventStore->loadFromStream($customerId);
        $this->assertCount(2, $eventStream->events);
        $this->assertEquals('2', $eventStream->version->toString());
    }

    public function testUpdateAggregate(): void
    {
        // create
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventStore = new EventStore(new InMemoryStorage());

        $this->assertEquals('test', $customer->getName());

        // insert
        $eventStore->appendToStream($customerId, Version::createFirstVersion(), $customer->getChanges());

        // read
        $eventStream = $eventStore->loadFromStream($customerId);

        // insert 2
        $customer = Customer::loadFromEvents($eventStream->events);
        $customer->setName('test2');
        $eventStore->appendToStream($customerId, $eventStream->version, $customer->getChanges());

        //read 2
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer = Customer::loadFromEvents($eventStream->events);

        $this->assertEquals('test2', $customer->getName());
        $this->assertEquals('3', $eventStream->version->toString());
        $this->assertCount(3, $eventStream->events);
    }
}