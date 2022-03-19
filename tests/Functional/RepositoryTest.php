<?php

namespace Tcieslar\EventStore\Tests\Functional;

use Tcieslar\EventStore\Aggregate\AggregateManager;
use Tcieslar\EventStore\ConcurrencyResolving\DoNothingStrategy;
use Tcieslar\EventStore\Snapshot\StoreStrategy\EachTimeStoreStrategy;
use Tcieslar\EventStore\Store\InMemoryEventStore;
use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Tests\Example\Aggregate\Order;
use Tcieslar\EventStore\Tests\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Tests\Example\Repository\CustomerRepository;
use Tcieslar\EventStore\Tests\Example\Repository\OrderRepository;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Snapshot\InMemorySnapshotRepository;
use Tcieslar\EventStore\Store\InMemoryEventStorage;
use PHPUnit\Framework\TestCase;

use Tcieslar\EventStore\Aggregate\UnitOfWork;

class RepositoryTest extends TestCase
{
    public function testAdd(): void
    {
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(),
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );
        $repository = new CustomerRepository($aggregateManager);

        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $repository->addAggregate($customer);

        $this->assertCount(0, $eventStore->getAllEvents());
        $aggregateManager->flush();

        $this->assertCount(2, $eventStore->getAllEvents());
    }

    public function testFind(): void
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
        $repository = new CustomerRepository($aggregateManager);

        // insert
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');
        $repository->addAggregate($customer);
        $aggregateManager->flush();
        $unitOfWork->reset();

        // find
        $customer = $repository->find($customerId);
        $this->assertNotNull($customer);
        $this->assertInstanceOf(Customer::class, $customer);

        // get the same
        $obj2 = $repository->find($customerId);
        $this->assertSame($obj2, $customer);
    }

    public function testScenario(): void
    {
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $snapshotRepository = new InMemorySnapshotRepository();
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new DoNothingStrategy(),
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );

        // insert

        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'name');
        $orderId = OrderId::create();
        $order = Order::create($orderId, 'description');

        $aggregateManager->addAggregate($customer);
        $aggregateManager->addAggregate($order);
        $aggregateManager->flush();
        $this->assertCount(3, $eventStore->getAllEvents());

        // assign relation

        $customer->addOrder($order);
        $aggregateManager->flush();
        $this->assertCount(4, $eventStore->getAllEvents());
        $this->assertSame($customer->getOrderIds()[0], $orderId);

        // get form repository
        $aggregateManager->reset();

        $customerRepository = new CustomerRepository($aggregateManager);
        $orderRepository = new OrderRepository($aggregateManager);
        $customer2 = $customerRepository->find($customerId);
        $order2 = $orderRepository->find($orderId);

        $this->assertSame($customer->getId(), $customer2->getId());
        $this->assertSame($order->getId(), $order2->getId());
    }
}