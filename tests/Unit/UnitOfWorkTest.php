<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Store\InMemoryEventStore;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Store\InMemoryEventStorage;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\UnitOfWork;
use Tcieslar\EventStore\Aggregate\Version;

/**
 * @group unit
 */
class UnitOfWorkTest extends TestCase
{
    public function testInsertAndGet(): void
    {
        $unitOfWork = new UnitOfWork();
        // new customer
        $customer = $this->createCustomer();
        $this->assertNull($unitOfWork->get($customer->getId()));

        // insert
        $unitOfWork->insert($customer);
        $aggregate = $unitOfWork->get($customer->getId());
        $this->assertNotNull($aggregate);
        $this->assertInstanceOf(Customer::class, $aggregate);

        // insert with version 0
        $reflectionClass = new ReflectionClass(UnitOfWork::class);
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);

        $this->assertEquals('0', $identityMap[$customer->getId()->toString()]['version']->toString());
    }

    public function testInsertException(): void
    {
        $unitOfWork = new UnitOfWork();
        // new customer
        $customer = $this->createCustomer();
        $this->assertNull($unitOfWork->get($customer->getId()));

        // insert
        $unitOfWork->insert($customer);
        $this->expectException(InvalidArgumentException::class);
        $unitOfWork->insert($customer);
    }

    public function testReset(): void
    {
        $unitOfWork = new UnitOfWork();

        $customer = $this->createCustomer();
        $unitOfWork->insert($customer);
        $unitOfWork->reset();

        // empty identityMap
        $reflectionClass = new ReflectionClass(UnitOfWork::class);
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);
        $this->assertEmpty($identityMap);
    }

    public function testPersist(): void
    {
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $unitOfWork = new UnitOfWork();

        // create outside
        $customer = $this->createCustomer();
        $customerId = $customer->getId();
        $eventStore->appendToStream($customer->getId(), $customer->getType(), Version::zero(), $customer->recordedEvents());
        unset($customer);

        // load and persist
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer2 = Customer::loadFromEvents($eventStream->events);
        $unitOfWork->persist($customer2, $eventStream->endVersion);

        $reflectionClass = new ReflectionClass(UnitOfWork::class);
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);

        // loaded with version 2
        $this->assertNotEmpty($identityMap);
        $this->assertEquals('2', $identityMap[$customerId->toString()]['version']->toString());
    }

    public function testChangeVersion(): void
    {
        $unitOfWork = new UnitOfWork();

        // create outside
        $customer = $this->createCustomer();
        $customerId = $customer->getId();

        $unitOfWork->insert($customer);

        // version 0
        $reflectionClass = new ReflectionClass(UnitOfWork::class);
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);

        $this->assertNotEmpty($identityMap);
        $this->assertEquals('0', $identityMap[$customerId->toString()]['version']->toString());

        $unitOfWork->changeVersion($customer, Version::number(123456));
        $identityMap = $reflectionProperty->getValue($unitOfWork);
        $this->assertEquals('123456', $identityMap[$customerId->toString()]['version']->toString());
    }

    public function testVersionException(): void
    {
        $unitOfWork = new UnitOfWork();
        $customer = $this->createCustomer();
        $this->assertNull($unitOfWork->get($customer->getId()));
        $this->expectException(InvalidArgumentException::class);
        $unitOfWork->getVersion($customer);
    }

    public function testGetVersion(): void
    {
        $unitOfWork = new UnitOfWork();
        $customer = $this->createCustomer();
        $unitOfWork->insert($customer);
        $this->assertEquals(Version::zero(), $unitOfWork->getVersion($customer));
    }

    private function createCustomer(): Customer
    {
        $customerId = new CustomerId(Uuid::v4());
        return Customer::create($customerId, 'name');
    }
}