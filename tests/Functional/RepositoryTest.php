<?php

namespace Functional;

use Aggregate\AggregateManager;
use ConcurrencyResolving\DoNothingStrategy;
use EventStore;
use Example\Aggregate\Customer;
use Example\Aggregate\CustomerId;
use Example\Aggregate\Order;
use Example\Aggregate\OrderId;
use Example\Repository\CustomerRepository;
use Example\Repository\OrderRepository;
use EventPublisher\FileEventPublisher;
use Snapshot\InMemorySnapshotRepository;
use Storage\InMemoryStorage;
use Utils\PhpSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Aggregate\UnitOfWork;

class RepositoryTest extends TestCase
{
    public function testAdd(): void
    {
        $eventStore = new EventStore(
            storage: new InMemoryStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore, snapshotRepository: new InMemorySnapshotRepository(
            serializer: new PhpSerializer()
        ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );
        $repository = new CustomerRepository($aggregateManager);

        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $repository->add($customer);

        $this->assertCount(0, $eventStore->getAllEvents());
        $aggregateManager->flush();

        $this->assertCount(2, $eventStore->getAllEvents());
    }

    public function testFind(): void
    {
        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: new EventStore(
                storage: new InMemoryStorage(),
                eventPublisher: new FileEventPublisher()
            ),
            snapshotRepository: new InMemorySnapshotRepository(
            serializer: new PhpSerializer()
        ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );
        $repository = new CustomerRepository($aggregateManager);

        // insert
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $repository->add($customer);
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
        $eventStore = new EventStore(
            storage: new InMemoryStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $snapshotRepository = new InMemorySnapshotRepository(
            serializer: new PhpSerializer()
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        // insert

        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'name');
        $orderId = new OrderId(Uuid::v4());
        $order = Order::create($orderId,'description');

        $aggregateManager->addAggregate($customer);
        $aggregateManager->addAggregate($order);
        $aggregateManager->flush();
        $this->assertCount(3, $eventStore->getAllEvents());

        // assign relation

        $customer->addOrder($order);
        $aggregateManager->flush();
        $this->assertCount(4, $eventStore->getAllEvents());
        $this->assertSame($customer->getOrderIds()[0],$orderId );

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