<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;

use Tcieslar\EventStore\Aggregate\Version;

class ConcurrentExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new ConcurrencyException(
            CustomerId::create()->getUuid(),
            new AggregateType(Customer::class),
            Version::number(123),
            Version::number(123),
            new EventCollection(),
            new EventCollection()
        );

        $this->assertNotNull($exception->aggregateId);
    }
}