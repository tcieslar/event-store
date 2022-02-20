<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\Version;

class ConcurrentExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new ConcurrencyException(
            new CustomerId(Uuid::v4()),
            new AggregateType(Customer::class),
            Version::createVersion(123),
            Version::createVersion(123),
            new EventCollection(),
            new EventCollection()
        );

        $this->assertNotNull($exception->aggregateId);
    }
}