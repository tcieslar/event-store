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
        $customer = Customer::create(new CustomerId(Uuid::v4()), 'test');
        $repository->add($customer);

        $this->assertCount(0, $eventStore->getAllEvents());
        $unitOfWork->flush();
        $this->assertCount(2, $eventStore->getAllEvents());
    }

}