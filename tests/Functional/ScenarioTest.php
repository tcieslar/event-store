<?php

namespace Functional;

use AggregateManager;
use DoNothingStrategy;
use Example\Aggregate\Customer;
use Example\Aggregate\CustomerId;
use Example\Aggregate\Order;
use Example\Aggregate\OrderId;
use Example\EventStore;
use Example\Repository\CustomerRepository;
use Example\Repository\OrderRepository;
use FileEventPublisher;
use InMemorySnapshotRepository;
use InMemoryStorage;
use PhpSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use UnitOfWork;

class ScenarioTest extends TestCase
{
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
        $this->assertSame($customer->getOrdersIds()[0],$orderId );

        // get form repository
        $aggregateManager->reset();

        $customerRepository = new CustomerRepository($aggregateManager);
        $orderRepository = new OrderRepository($aggregateManager);
        $customer2 = $customerRepository->find($customerId);
        $order2 = $orderRepository->find($orderId);

        $this->assertSame($customer->getId(), $customer2->getId());
        $this->assertSame($order->getId(), $order2->getId());

        //todo:
        //var_dump($snapshotRepository->getSnapshot($customer->getId()));
    }

}