<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;

use Tcieslar\EventStore\Aggregate\Version;

class EventStreamTest extends TestCase
{
    public function testIsEmpty(): void
    {
        $eventStream = new EventStream(
            CustomerId::create()->getUuid(),
            new AggregateType(Customer::class),
            Version::number(1),
            Version::number(1),
            new EventCollection()
        );

        $this->assertTrue($eventStream->isEmpty());
    }
}