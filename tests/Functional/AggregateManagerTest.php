<?php

namespace Functional;

use AggregateManager;
use DoNothingStrategy;
use EventCollection;
use Example\Customer;
use Example\CustomerCreatedEvent;
use Example\CustomerCredentialSetEvent;
use Example\CustomerId;
use Example\EventStore;
use Example\Order;
use Example\OrderId;
use InMemorySnapshotRepository;
use InMemoryStorage;
use PhpSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use UnitOfWork;
use Version;

class AggregateManagerTest extends TestCase
{
    public function testAddGetAggregate(): void
    {
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: new EventStore(
                storage: new InMemoryStorage()
            ),
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        // add
        $customer = $this->createCustomer();
        $this->assertNull($aggregateManager->getAggregate($customer->getId()));
        $aggregateManager->addAggregate($customer);

        //get
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
                $this->callback(function (EventCollection $changes) {
                    return count($changes) === 2 &&
                        $changes->get(0) instanceof CustomerCreatedEvent &&
                        $changes->get(1) instanceof CustomerCredentialSetEvent;
                })
            )
            ->willReturn(Version::createVersion(2));

        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager(
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
        $this->assertEmpty($customer->recordedEvents());

        // check identityMap version number
        $identityMap = $unitOfWork->getIdentityMap();
        $this->assertSame('2', $identityMap[$customer->getId()->toString()]['version']->toString());
    }

    public function testSecondFlush(): void
    {
        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: new EventStore(new InMemoryStorage()),
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        $customer = $this->createCustomer();

        // flush
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();
        $aggregateManager->flush();

        // check identityMap version number
        $identityMap = $unitOfWork->getIdentityMap();
        $this->assertSame('2', $identityMap[$customer->getId()->toString()]['version']->toString());
    }

    public function testMultiAggregateFlush(): void
    {
        $unitOfWork = new UnitOfWork();
        $eventStore = new EventStore(new InMemoryStorage());
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        // create
        $customerA = $this->createCustomer();
        $customerB = $this->createCustomer();
        $customerC = $this->createCustomer();

        // flush
        $aggregateManager->addAggregate($customerA);
        $aggregateManager->addAggregate($customerB);
        $aggregateManager->addAggregate($customerC);
        $aggregateManager->flush();

        // check identityMap version number
        $this->assertSame('2', $unitOfWork->getIdentityMap()[$customerA->getId()->toString()]['version']->toString());
        $this->assertSame('2', $unitOfWork->getIdentityMap()[$customerB->getId()->toString()]['version']->toString());
        $this->assertSame('2', $unitOfWork->getIdentityMap()[$customerC->getId()->toString()]['version']->toString());
        $this->assertCount(6, $eventStore->getAllEvents());

        // update after flush
        $customerA->setName('test4');
        $customerC->setName('test5');

        $this->assertCount(1, $customerA->recordedEvents());
        $this->assertCount(0, $customerB->recordedEvents());
        $this->assertCount(1, $customerC->recordedEvents());

        // second flush
        $aggregateManager->flush();

        $this->assertCount(0, $customerA->recordedEvents());
        $this->assertCount(0, $customerC->recordedEvents());
        $this->assertSame('3', $unitOfWork->getIdentityMap()[$customerC->getId()->toString()]['version']->toString());
        $this->assertCount(8, $eventStore->getAllEvents());
    }

    public function testMultiEventFlush(): void
    {
        $unitOfWork = new UnitOfWork();
        $eventStore = new EventStore(new InMemoryStorage());
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        // create and modify
        $customerA = $this->createCustomer();
        $customerA->addOrder(
            new Order(
                new OrderId(Uuid::v4()),
                'Order 1',
                new \DateTimeImmutable()
            )
        );
        $customerA->setName('Test 2');
        $customerA->addOrder(
            new Order(
                new OrderId(Uuid::v4()),
                'Order 2',
                new \DateTimeImmutable()
            )
        );
        $aggregateManager->addAggregate($customerA);

        // flush
        $aggregateManager->flush();
        $this->assertSame('5', $unitOfWork->getIdentityMap()[$customerA->getId()->toString()]['version']->toString());
    }

    private function createCustomer(): Customer
    {
        $customerId = new CustomerId(Uuid::v4());
        return Customer::create($customerId, 'name');
    }
}