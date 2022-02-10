<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CustomerAggregateTest extends TestCase
{
    public function testCreateAggregate(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');

        $this->assertSame($customer->getId(), $customerId);
        $changes = $customer->recordedEvents();
        $this->assertNotEmpty($changes);
        $this->assertInstanceOf(CustomerCreatedEvent::class, $changes->get(0));
        $this->assertInstanceOf(CustomerCredentialSetEvent::class, $changes->get(1));
    }
}