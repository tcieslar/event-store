<?php

namespace Unit;

use DoNothingStrategy;
use Example\Customer;
use Example\CustomerCreatedEvent;
use Example\CustomerCredentialSetEvent;
use Example\CustomerId;
use Example\EventStore;
use InMemorySnapshotRepository;
use InMemoryStorage;
use PhpSerializer;
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
        $unitOfWork = new UnitOfWork(
            eventStore: new EventStore(
                storage: new InMemoryStorage()
            ),
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );
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

    public function testFlush(): void
    {
        $customer = $this->createCustomer();

        // append To Stream
        $eventStore = $this->createMock(EventStore::class);
        $eventStore->expects($this->once())
            ->method('appendToStream')
            ->with($this->equalTo($customer->getId()),
                $this->equalTo(Version::createFirstVersion()),
                $this->callback(function (\EventCollection $changes) {
                    return count($changes) === 2 &&
                        $changes->get(0) instanceof CustomerCreatedEvent &&
                        $changes->get(1) instanceof CustomerCredentialSetEvent;
                })
            );

        $unitOfWork = new UnitOfWork(
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        $unitOfWork->insert($customer);
        $unitOfWork->flush();
    }

    public function testReset(): void
    {
        $unitOfWork = new UnitOfWork(
            eventStore: new EventStore(
                storage: new InMemoryStorage()
            ),
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

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
        $unitOfWork = new UnitOfWork(
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        // create outside unitofWork
        $customer = $this->createCustomer();
        $customerId = $customer->getId();
        $eventStore->appendToStream($customer->getId(), Version::createFirstVersion(), $customer->getChanges());
        unset($customer);

        // load
        $eventStream = $unitOfWork->loadAggregateEventStream($customerId);
        $customer2= Customer::loadFromEvents($eventStream->events);
        $unitOfWork->persist($customer2, $eventStream->version);

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