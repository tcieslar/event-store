<?php

use Example\Customer;
use Example\CustomerId;
use Example\CustomerRepository;
use Example\EventStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CustomerRepositoryTest extends TestCase
{
    public function testAdd(): void
    {
        $inMemoryStorage = new InMemoryStorage();
        $eventStore = new EventStore($inMemoryStorage);
        $strategy = new DoNothingStrategy();
        $unitOfWork = new UnitOfWork($eventStore, new InMemorySnapshotRepository(new PhpSerializer()), $strategy);
        $repository = new CustomerRepository($unitOfWork);
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $repository->add($customer);

        $this->assertCount(0, $eventStore->getAllEvents());
        $unitOfWork->flush();
        $this->assertCount(2, $eventStore->getAllEvents());
    }

    public function testFind(): void
    {
        // insert
        $inMemoryStorage = new InMemoryStorage();
        $eventStore = new EventStore($inMemoryStorage);
        $strategy = new DoNothingStrategy();
        $unitOfWork = new UnitOfWork($eventStore, new InMemorySnapshotRepository(new PhpSerializer()), $strategy);
        $repository = new CustomerRepository($unitOfWork);
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $repository->add($customer);
        $unitOfWork->flush();
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