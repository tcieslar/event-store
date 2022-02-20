<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\Version;

class EventStreamTest extends TestCase
{
    public function testIsEmpty(): void
    {
        $eventStream = new EventStream(
            new CustomerId(Uuid::v4()),
            new AggregateType(Customer::class),
            Version::number(1),
            Version::number(1),
            new EventCollection()
        );

        $this->assertTrue($eventStream->isEmpty());
    }
}