<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventSourcing\EventAggregateMismatchException;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Tests\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Tests\Example\Event\OrderCreatedEvent;


class CustomerTest extends TestCase
{
    public function testName(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'name');

        $this->assertEquals('name', $customer->getName());
        $customer->setName('name2');
        $this->assertEquals('name2', $customer->getName());
    }

    public function testEmptyName(): void
    {
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'name');

        $this->expectException(\InvalidArgumentException::class);
        $customer->setName('');
    }

    public function testEventAggregateMismatchException(): void
    {
        $orderCreatedEvent = new OrderCreatedEvent(
            OrderId::create(),
            'test'
        );

        $this->expectException(EventAggregateMismatchException::class);

        Customer::loadFromEvents(new EventCollection([
            $orderCreatedEvent
        ]));
    }

}