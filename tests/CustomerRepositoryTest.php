<?php

use Example\Customer;
use Example\CustomerId;
use Example\CustomerRepository;
use Example\EventStoreInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CustomerRepositoryTest extends TestCase
{
    public function testAdd(): void
    {
        $eventStore = new EventStoreInMemory();
        $unitOfWork = new UnitOfWork($eventStore);
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
        $eventStore = new EventStoreInMemory();
        $unitOfWork = new UnitOfWork($eventStore);
        $repository = new CustomerRepository($unitOfWork);
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $repository->add($customer);
        $unitOfWork->flush();
        $unitOfWork->reset();

        // find
        $obj = $repository->find($customerId);
        $this->assertNotNull($obj);
        $this->assertInstanceOf(Customer::class, $obj);

        // get the same
        $obj2 = $repository->find($customerId);
        $this->assertSame($obj2, $obj);
    }
}