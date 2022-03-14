<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Tests\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;


final class CustomerAggregateTest extends TestCase
{
    public function testCreateAggregate(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'test');

        $this->assertSame($customer->getId(), $customerId->getUuid());
        $changes = $customer->recordedEvents();
        $this->assertNotEmpty($changes);
        $this->assertInstanceOf(CustomerCreatedEvent::class, $changes->get(0));
        $this->assertInstanceOf(CustomerCredentialSetEvent::class, $changes->get(1));
    }
}