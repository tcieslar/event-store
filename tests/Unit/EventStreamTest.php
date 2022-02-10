<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventStream;
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
            Version::createVersion(1),
            Version::createVersion(1),
            new EventCollection()
        );

        $this->assertTrue($eventStream->isEmpty());
    }
}