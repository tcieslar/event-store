<?php

namespace Functional;

use AggregateManager;
use DoNothingStrategy;
use Example\Customer;
use Example\CustomerId;
use Example\CustomerRepository;
use Example\EventStore;
use FileEventPublisher;
use InMemorySnapshotRepository;
use InMemoryStorage;
use PhpSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use UnitOfWork;

class CustomerRepositoryTest extends TestCase
{
    public function testAdd(): void
    {
        $eventStore = new EventStore(
            storage: new InMemoryStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $strategy = new DoNothingStrategy();
        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager($unitOfWork, $eventStore, new InMemorySnapshotRepository(new PhpSerializer()), $strategy);
        $repository = new CustomerRepository($aggregateManager);

        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $repository->add($customer);

        $this->assertCount(0, $eventStore->getAllEvents());
        $aggregateManager->flush();
       // $aggregateManager->flush();

        $this->assertCount(2, $eventStore->getAllEvents());
    }

    public function testFind(): void
    {
        // insert
        $eventStore = new EventStore(
            storage: new InMemoryStorage(),
            eventPublisher: new FileEventPublisher()
        );
        $strategy = new DoNothingStrategy();
        $unitOfWork = new UnitOfWork();
        $aggregateManager = new AggregateManager($unitOfWork, $eventStore, new InMemorySnapshotRepository(new PhpSerializer()), $strategy);
        $repository = new CustomerRepository($aggregateManager);
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
}