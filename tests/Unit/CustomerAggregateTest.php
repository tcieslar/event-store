<?php

namespace Unit;

use Example\Customer;
use Example\CustomerCreatedEvent;
use Example\CustomerCredentialSetEvent;
use Example\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CustomerAggregateTest extends TestCase
{
    public function testCreateAggregate(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');

        $this->assertSame($customer->getId(), $customerId);
        $changes = $customer->getChanges();
        $this->assertNotEmpty($changes);
        $this->assertInstanceOf(CustomerCreatedEvent::class, $changes->get(0));
        $this->assertInstanceOf(CustomerCredentialSetEvent::class, $changes->get(1));
    }
}