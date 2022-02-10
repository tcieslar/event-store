<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CustomerTest extends TestCase
{
    public function testName(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'name');

        $this->assertEquals('name', $customer->getName());
        $customer->setName('name2');
        $this->assertEquals('name2', $customer->getName());
    }

    public function testEmptyName(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'name');

        $this->expectException(\InvalidArgumentException::class);
        $customer->setName('');
    }

}