<?php

use Example\Customer;
use Example\CustomerId;
use Example\EventStoreInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class EventStoreInMemoryTest extends TestCase
{
    public function testLoadEmpty(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $eventStream = new EventStoreInMemory();

        $this->expectExceptionMessage('Aggregate not found.');
        $eventStream->loadEventStream($customerId);
    }

    public function testNewAggregate(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');

        $eventStore = new EventStoreInMemory();
        $eventStore->appendToStream($customerId, Version::createFirstVersion(), $customer->getChanges());

        $eventStream = $eventStore->loadEventStream($customerId);
        $this->assertEquals('2', $eventStream->version->toString());
        $this->assertCount(2, $eventStream->events);
    }

    public function testUpdateAggregate(): void
    {
        // create
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventStore = new EventStoreInMemory();

        // insert
        $eventStore->appendToStream($customerId, Version::createFirstVersion(), $customer->getChanges());

        // read
        $eventStream = $eventStore->loadEventStream($customerId);

        // insert 2
        $customer = Customer::loadFromEvents($eventStream->events);
        $customer->setName('test2');
        $eventStore->appendToStream($customerId, $eventStream->version, $customer->getChanges());

        //read 2
        $eventStream = $eventStore->loadEventStream($customerId);

        $this->assertEquals('3', $eventStream->version->toString());
        $this->assertCount(3, $eventStream->events);
    }
}