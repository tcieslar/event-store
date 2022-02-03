<?php

use Example\Customer;
use Example\CustomerId;
use Example\CustomerRepository;
use Example\EventStoreInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CustomerRepositoryTest extends TestCase
{
    public function testRepository(): void
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

        $c2 = $repository->find($customerId);
        $this->assertSame($customer->getId(), $c2->getId());

        $customerId2 = new CustomerId(Uuid::v4());
        //$c3 = $repository->find($customerId2);
    }

}