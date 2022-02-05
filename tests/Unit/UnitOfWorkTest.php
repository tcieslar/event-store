<?php

namespace Unit;

use Example\Customer;
use Example\CustomerId;
use Example\EventStore;
use InMemoryStorage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Uid\Uuid;
use UnitOfWork;
use Version;

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
        $reflectionClass = new ReflectionClass('UnitOfWork');
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);

        $this->assertEquals('0', $identityMap[$customer->getId()->toString()]['version']->toString());
    }

    public function testReset(): void
    {
        $unitOfWork = new UnitOfWork();

        $customer = $this->createCustomer();
        $unitOfWork->insert($customer);
        $unitOfWork->reset();

        // empty identityMap
        $reflectionClass = new ReflectionClass('UnitOfWork');
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);

        $this->assertEmpty($identityMap);
    }

    public function testPersist(): void
    {
        $eventStore = new EventStore(
            storage: new InMemoryStorage()
        );
        $unitOfWork = new UnitOfWork();

        // create outside
        $customer = $this->createCustomer();
        $customerId = $customer->getId();
        $eventStore->appendToStream($customer->getId(), Version::createFirstVersion(), $customer->getChanges());
        unset($customer);

        // load and persist
        $eventStream = $eventStore->loadFromStream($customerId);
        $customer2 = Customer::loadFromEvents($eventStream->events);
        $unitOfWork->persist($customer2, $eventStream->endVersion);

        $reflectionClass = new ReflectionClass('UnitOfWork');
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);

        // loaded with version 2
        $this->assertNotEmpty($identityMap);
        $this->assertEquals('2', $identityMap[$customerId->toString()]['version']->toString());
    }

    private function createCustomer(): Customer
    {
        $customerId = new CustomerId(Uuid::v4());
        return Customer::create($customerId, 'name');
    }
}