<?php

namespace Unit;

use Event\EventCollection;
use Event\EventStream;
use Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Aggregate\Version;

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