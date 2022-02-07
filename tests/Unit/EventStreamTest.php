<?php

namespace Unit;

use EventCollection;
use EventStream;
use Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Version;

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