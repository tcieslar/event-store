<?php

namespace Tcieslar\EventStore\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\EventPublisher\EventPublisherInterface;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Store\PsqlEventStore;
use Tcieslar\EventStore\Utils\EventSerializerInterface;
use Tcieslar\EventStore\Utils\SymfonySerializerAdapter;

/**
 * @group integration
 */
class PsqlEventStoreTest extends TestCase
{
    private static string $postgreUrl = 'postgres:test@localhost:5432/event_store?serverVersion=14&charset=utf8';

    public function testAppendAndLoad(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'name 1');
        $aggregateType = AggregateType::byAggregate($customer);
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(fn(EventCollection $events) => $events->count() === 2 &&
                $events->get(0) instanceof CustomerCreatedEvent &&
                $events->get(1) instanceof CustomerCredentialSetEvent
            ));
        $eventStore = new PsqlEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
        $eventStore->appendToStream($customer->getId(),
            $aggregateType,
            Version::zero(),
            $customer->recordedEvents()
        );
        $eventStream = $eventStore->loadFromStream($customerId->getUuid());

        $this->assertEquals($customerId->getUuid(), $eventStream->aggregateId);
        $this->assertEquals(Version::zero(), $eventStream->startVersion);
        $this->assertEquals(Version::number(2), $eventStream->endVersion);
        $this->assertEquals($aggregateType, $eventStream->aggregateType);
        $this->assertCount(2, $eventStream->events);
    }

    public function testLoadEmpty(): void
    {
        $customerId = CustomerId::create();
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->never())
            ->method('publish');
        $eventStore = new PsqlEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);

        $this->expectExceptionMessage('Aggregate not found.');
        $eventStore->loadFromStream($customerId->getUuid());
    }

    public function testNewAggregate(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $aggregateType = AggregateType::byAggregate($customer);
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(fn(EventCollection $events) => $events->count() === 2 &&
                $events->get(0) instanceof CustomerCreatedEvent &&
                $events->get(1) instanceof CustomerCredentialSetEvent
            ));
        $eventStore = new PsqlEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
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
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->exactly(2))
            ->method('publish');
        $eventStore = new PsqlEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
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
        $this->assertCount(3, $eventStream->events);
    }

    public function testLoadFromStream(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $aggregateType = AggregateType::byAggregate($customer);
        $customer->setName('test 2');
        $customer->setName('test 3');
        $customer->setName('test 4');
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(fn(EventCollection $events) => $events->count() === 5 &&
                $events->get(0) instanceof CustomerCreatedEvent &&
                $events->get(1) instanceof CustomerCredentialSetEvent &&
                $events->get(2) instanceof CustomerCredentialSetEvent &&
                $events->get(3) instanceof CustomerCredentialSetEvent &&
                ($e5 = $events->get(4)) instanceof CustomerCredentialSetEvent &&
                $e5->getName() === 'test 4'

            ));
        $eventStore = new PsqlEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
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

    private function getSerializer(): EventSerializerInterface
    {
        return new SymfonySerializerAdapter();
    }
}