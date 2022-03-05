<?php

namespace Tcieslar\EventStore\Tests\Functional;

use Tcieslar\EventStore\Aggregate\AggregateManager;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\ConcurrencyResolving\DoNothingStrategy;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Snapshot\StoreStrategy\EachTimeStoreStrategy;
use Tcieslar\EventStore\Store\InMemoryEventStore;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\Order;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Snapshot\InMemorySnapshotRepository;
use Tcieslar\EventStore\Store\InMemoryEventStorage;
use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Aggregate\UnitOfWork;
use Tcieslar\EventStore\Aggregate\Version;

class AggregateManagerTest extends TestCase
{
    public function testFlush(): void
    {
        $customer = $this->createCustomer();
        $aggregateType = AggregateType::byAggregate($customer);

        // append To Stream
        $eventStore = $this->createMock(InMemoryEventStore::class);
        $eventStore->expects($this->once())
            ->method('appendToStream')
            ->with($this->equalTo($customer->getUuid()),
                $this->equalTo(
                    $aggregateType
                ),
                $this->equalTo(Version::zero()),
                $this->callback(function (EventCollection $changes) {
                    return count($changes) === 2 &&
                        $changes->get(0) instanceof CustomerCreatedEvent &&
                        $changes->get(1) instanceof CustomerCredentialSetEvent;
                })
            )
            ->willReturn(Version::number(2));

        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(),
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );

        // flush
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();

        // check aggregate
        $this->assertEmpty($customer->recordedEvents());

        // check identityMap version number
        $identityMap = $unitOfWork->getIdentityMap();
        $this->assertSame('2', $identityMap[$customer->getUuid()->toString()]['version']->toString());
    }

    public function testSecondFlush(): void
    {
        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: new InMemoryEventStore(
                storage: new InMemoryEventStorage(),
                eventPublisher: new FileEventPublisher()
            ),
            snapshotRepository: new InMemorySnapshotRepository(),
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );

        $customer = $this->createCustomer();

        // flush
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();
        $aggregateManager->flush();

        // check identityMap version number
        $identityMap = $unitOfWork->getIdentityMap();
        $this->assertSame('2', $identityMap[$customer->getUuid()->toString()]['version']->toString());
    }

    public function testMultiAggregateFlush(): void
    {
        $unitOfWork = new UnitOfWork();
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(),
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
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
        $this->assertSame('2', $unitOfWork->getIdentityMap()[$customerA->getUuid()->toString()]['version']->toString());
        $this->assertSame('2', $unitOfWork->getIdentityMap()[$customerB->getUuid()->toString()]['version']->toString());
        $this->assertSame('2', $unitOfWork->getIdentityMap()[$customerC->getUuid()->toString()]['version']->toString());
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
        $this->assertSame('3', $unitOfWork->getIdentityMap()[$customerC->getUuid()->toString()]['version']->toString());
        $this->assertCount(8, $eventStore->getAllEvents());
    }

    public function testMultiEventFlush(): void
    {
        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: new InMemoryEventStore(
                storage: new InMemoryEventStorage(),
                eventPublisher: new FileEventPublisher()
            ),
            snapshotRepository: new InMemorySnapshotRepository(),
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );

        // create and modify
        $customerA = $this->createCustomer();
        $customerA->addOrder(
            Order::create(
                OrderId::create(),
                'Order 1',
            )
        );
        $customerA->setName('Test 2');
        $customerA->addOrder(
            Order::create(
                OrderId::create(),
                'Order 2',
            )
        );
        $aggregateManager->addAggregate($customerA);

        // flush
        $aggregateManager->flush();
        $this->assertSame('5', ($unitOfWork)->getIdentityMap()[$customerA->getUuid()->toString()]['version']->toString());
    }

//    public function testWrongAggregateType(): void
//    {
//        $aggregateManager = new AggregateManager(
//            unitOfWork: new UnitOfWork(),
//            eventStore: new InMemoryEventStore(
//                storage: new InMemoryEventStorage(),
//                eventPublisher: new FileEventPublisher()
//            ),
//            snapshotRepository: new InMemorySnapshotRepository(
//                serializer: new PhpSerializer()
//            ),
//            concurrencyResolvingStrategy: new DoNothingStrategy()
//        );
//
//        $customer = $this->createCustomer();
//        $aggregateManager->addAggregate($customer);
//        $aggregateManager->flush();
//        $aggregateManager->reset();
//
//        $this->expectException(EventAggregateMismatchException::class);
//        $aggregateManager->findAggregate(Order::class, $customer->getId());
//    }

    public function testSnapshotLoad(): void
    {
        $snapshotRepository = new InMemorySnapshotRepository();
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: new InMemoryEventStore(
                storage: new InMemoryEventStorage(),
                eventPublisher: new FileEventPublisher()
            ),
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );

        // insert
        $customer = $this->createCustomer();
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();
        $aggregateManager->reset();

        // loading from store, snapshot saved
        $customer = $aggregateManager->findAggregate($customer->getUuid());
        $snapshot = $snapshotRepository->getSnapshot($customer->getUuid());
        $this->assertNotNull($snapshot);
        $this->assertEquals($customer, $snapshot->aggregate);
        $aggregateManager->reset();

        // loading from snapshot
        $customer2 = $aggregateManager->findAggregate($customer->getUuid());
        $this->assertEquals($customer, $customer2);
    }

    public function testSnapshotLoadWithEventReply(): void
    {
        $snapshotRepository = new InMemorySnapshotRepository();
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: new InMemoryEventStore(
                storage: new InMemoryEventStorage(),
                eventPublisher: new FileEventPublisher()
            ),
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );

        // insert
        $customer = $this->createCustomer();
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();
        $aggregateManager->reset();

        // loading from store, snapshot saved
        $customer = $aggregateManager->findAggregate($customer->getUuid());
        $snapshot = $snapshotRepository->getSnapshot($customer->getUuid());

        $this->assertNotNull($snapshot);
        $this->assertEquals($customer, $snapshot->aggregate);
        $aggregateManager->reset();


        // loading from snapshot
        /** @var Customer $customer2 */
        $customer2 = $aggregateManager->findAggregate($customer->getUuid());

        // store new event
        $customer2->setName('test 3');
        $aggregateManager->flush();
        $aggregateManager->reset();

        // load from snapshot with additional event
        $customer3 = $aggregateManager->findAggregate($customer->getUuid());
        $this->assertEquals('test 3', $customer3->getName());
    }

//    public function testTypeMismatchException(): void
//    {
//        $aggregateManager = new AggregateManager(
//            unitOfWork: new UnitOfWork(),
//            eventStore: new InMemoryEventStore(
//                storage: new InMemoryEventStorage(),
//                eventPublisher: new FileEventPublisher()
//            ),
//            snapshotRepository: new InMemorySnapshotRepository(
//                serializer: new PhpSerializer()
//            ),
//            concurrencyResolvingStrategy: new DoNothingStrategy()
//        );
//
//        // create Customer
//        $customer = $this->createCustomer();
//        $aggregateManager->addAggregate($customer);
//        $aggregateManager->flush();
//        $aggregateManager->reset();
//
//        $this->expectException(EventAggregateMismatchException::class);
//
//        // try to load Customer as Order
//        $aggregateManager->findAggregate(Order::class, $customer->getId());
//    }

    private function createCustomer(): Customer
    {
        $customerId = CustomerId::create();
        return Customer::create($customerId, 'name');
    }
}