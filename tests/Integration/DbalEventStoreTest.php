<?php

namespace Tcieslar\EventStore\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\EventPublisher\EventPublisherInterface;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Store\DbalEventStore;

/**
 * @group integration
 */
class DbalEventStoreTest extends TestCase
{
    private static string $postgreUrl = 'postgresql://postgres:test@localhost:5432/event_store?serverVersion=14&charset=utf8';

    public function testAppendAndLoad(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'name 1');
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(fn(EventCollection $events) =>
                $events->count() === 2 &&
                $events->get(0) instanceof CustomerCreatedEvent &&
                $events->get(1) instanceof CustomerCredentialSetEvent
            ));
        $eventStore = new DbalEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
        $eventStore->appendToStream($customer->getId(),
            $customer->getType(),
            Version::zero(),
            $customer->recordedEvents()
        );
        $eventStream = $eventStore->loadFromStream($customerId);

        $this->assertEquals($customerId, $eventStream->aggregateId);
        $this->assertEquals(Version::zero(), $eventStream->startVersion);
        $this->assertEquals(Version::number(2), $eventStream->endVersion);
        $this->assertEquals($customer->getType(), $eventStream->aggregateType);
        $this->assertCount(2, $eventStream->events);
    }

    public function testLoadEmpty(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->never())
            ->method('publish');
        $eventStore = new DbalEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);

        $this->expectExceptionMessage('Aggregate not found.');
        $eventStore->loadFromStream($customerId);
    }

    public function testNewAggregate(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(fn(EventCollection $events) =>
                $events->count() === 2 &&
                $events->get(0) instanceof CustomerCreatedEvent &&
                $events->get(1) instanceof CustomerCredentialSetEvent
            ));
        $eventStore = new DbalEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
        $eventStore->appendToStream($customerId, $customer->getType(), Version::zero(), $customer->recordedEvents());

        $eventStream = $eventStore->loadFromStream($customerId);
        $this->assertCount(2, $eventStream->events);
        $this->assertEquals('0', $eventStream->startVersion->toString());
        $this->assertEquals('2', $eventStream->endVersion->toString());
    }

    public function testUpdateAggregate(): void
    {
        // create
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->exactly(2))
            ->method('publish');
        $eventStore = new DbalEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
        $this->assertEquals('test', $customer->getName());

        // insert
        $eventStore->appendToStream($customerId, $customer->getType(), Version::zero(), $customer->recordedEvents());

        // read
        $eventStream = $eventStore->loadFromStream($customerId);

        // insert 2
        $customer = Customer::loadFromEvents($eventStream->events);
        $customer->setName('test2');
        $eventStore->appendToStream($customerId, $customer->getType(), $eventStream->endVersion, $customer->recordedEvents());

        //read 2
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer = Customer::loadFromEvents($eventStream->events);
        $this->assertEquals('test2', $customer->getName());
        $this->assertEquals('3', $eventStream->endVersion->toString());
        $this->assertCount(3, $eventStream->events);
    }

    public function testLoadFromStream(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $customer->setName('test 2');
        $customer->setName('test 3');
        $customer->setName('test 4');
        $eventPublisher = $this->createMock(EventPublisherInterface::class);
        $eventPublisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->callback(fn(EventCollection $events) =>
                $events->count() === 5 &&
                $events->get(0) instanceof CustomerCreatedEvent &&
                $events->get(1) instanceof CustomerCredentialSetEvent &&
                $events->get(2) instanceof CustomerCredentialSetEvent &&
                $events->get(3) instanceof CustomerCredentialSetEvent &&
                $events->get(4) instanceof CustomerCredentialSetEvent
            ));
        $eventStore = new DbalEventStore(self::$postgreUrl, $this->getSerializer(), $eventPublisher);
        $eventStore->appendToStream($customerId, $customer->getType(), Version::zero(), $customer->recordedEvents());

        $eventStream = $eventStore->loadFromStream($customerId, Version::zero());
        $this->assertCount(5, $eventStream->events);
        $eventStream = $eventStore->loadFromStream($customerId, Version::number(1));
        $this->assertCount(4, $eventStream->events);
        $eventStream = $eventStore->loadFromStream($customerId, Version::number(2));
        $this->assertCount(3, $eventStream->events);
        $eventStream = $eventStore->loadFromStream($customerId, Version::number(4));
        $this->assertCount(1, $eventStream->events);
    }

    private function getSerializer(): SerializerInterface
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        return new Serializer($normalizers, $encoders);
    }
}