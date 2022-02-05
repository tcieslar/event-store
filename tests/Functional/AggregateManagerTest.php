<?php

namespace Functional;

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

class AggregateManagerTest extends TestCase
{
    //todo:
    public function testAddGetAggregate(): void
    {
        $aggregateManager = new \AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: new EventStore(
                storage: new InMemoryStorage()
            ),
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        $customer = $this->createCustomer();
        $this->assertNull($aggregateManager->getAggregate($customer->getId()));
        $aggregateManager->addAggregate($customer);
        $customer2 = $aggregateManager->getAggregate($customer->getId());
        $this->assertSame($customer, $customer2);
    }

    public function testFlush(): void
    {
        $customer = $this->createCustomer();

        // append To Stream
        $eventStore = $this->createMock(EventStore::class);
        $eventStore->expects($this->once())
            ->method('appendToStream')
            ->with($this->equalTo($customer->getId()),
                $this->equalTo(Version::createZeroVersion()),
                $this->callback(function (\EventCollection $changes) {
                    return count($changes) === 2 &&
                        $changes->get(0) instanceof CustomerCreatedEvent &&
                        $changes->get(1) instanceof CustomerCredentialSetEvent;
                })
            )
            ->willReturn(Version::createVersion(2));

        $unitOfWork = new UnitOfWork();
        $aggregateManager = new \AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        // flush
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();

        // check aggregate
        $this->assertEmpty($customer->getChanges());

        // check identityMap version number
        $reflectionClass = new ReflectionClass('UnitOfWork');
        $reflectionProperty = $reflectionClass->getProperty('identityMap');
        $identityMap = $reflectionProperty->getValue($unitOfWork);
        $this->assertSame('2', $identityMap[$customer->getId()->toString()]['version']->toString());
    }

    private function createCustomer(): Customer
    {
        $customerId = new CustomerId(Uuid::v4());
        return Customer::create($customerId, 'name');
    }
}