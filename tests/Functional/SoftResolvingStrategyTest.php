<?php

namespace Tcieslar\EventStore\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\AggregateManager;
use Tcieslar\EventStore\Aggregate\UnitOfWork;
use Tcieslar\EventStore\ConcurrencyResolving\SoftResolvingStrategy;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\Order;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Exception\AggregateReloadNeedException;
use Tcieslar\EventStore\Exception\RealConcurrencyException;
use Tcieslar\EventStore\Snapshot\InMemorySnapshotRepository;
use Tcieslar\EventStore\Store\InMemoryEventStorage;
use Tcieslar\EventStore\Store\InMemoryEventStore;
use Tcieslar\EventStore\Utils\PhpSerializer;

class SoftResolvingStrategyTest extends TestCase
{
    public function testSameEventCollision(): void
    {
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $snapshotRepository = new InMemorySnapshotRepository(
            serializer: new PhpSerializer()
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new SoftResolvingStrategy($eventStore)
        );

        $aggregateManager2 = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new SoftResolvingStrategy($eventStore)
        );

        //create, first thread
        $customer = $this->createCustomer();
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();

        //read and change, second thread
        /** @var Customer $customer2 */
        $customer2 = $aggregateManager2->findAggregate(Customer::class, $customer->getId());
        $customer2->setName('name 2');
        $aggregateManager2->flush();

        $this->expectException(RealConcurrencyException::class);

        //store same event in first thread
        $customer->setName('name 3');
        $aggregateManager->flush();
    }

    public function testDifferentEventCollision(): void
    {
        $eventStore = new InMemoryEventStore(
            storage: new InMemoryEventStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $snapshotRepository = new InMemorySnapshotRepository(
            serializer: new PhpSerializer()
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new SoftResolvingStrategy($eventStore)
        );

        $aggregateManager2 = new AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: new SoftResolvingStrategy($eventStore)
        );

        //create, first thread
        $customer = $this->createCustomer();
        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();

        //read and change, second thread
        /** @var Customer $customer2 */
        $customer2 = $aggregateManager2->findAggregate(Customer::class, $customer->getId());
        $customer2->setName('name 2');
        $aggregateManager2->flush();

        //store different event in first thread
        $customer->addOrder(Order::create(new OrderId(Uuid::v4()), 'test desc'));

        try{
            $aggregateManager->flush();
        } catch (AggregateReloadNeedException $exception) {
            $this->assertEquals($customer->getId(), $exception->aggregateId);
        }

        $this->assertCount(4, $eventStore->getAllEvents());
        $customer3 = $aggregateManager->findAggregate(Customer::class, $customer->getId());
        $this->assertEquals('name 2', $customer3->getName());
        $this->assertCount(1, $customer3->getOrderIds());
    }

    private function createCustomer(): Customer
    {
        $customerId = new CustomerId(Uuid::v4());
        return Customer::create($customerId, 'name');
    }
}