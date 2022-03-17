<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Aggregate\AggregateType;
use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;

class AggregateTypeTest extends TestCase
{
    public function testGetTypeName(): void
    {
        $customer = Customer::create(
            CustomerId::create(),
            'test'
        );
        $aggregateType = AggregateType::byAggregate($customer);

        $this->assertEquals('Customer', $aggregateType->getTypeName());
    }
}
