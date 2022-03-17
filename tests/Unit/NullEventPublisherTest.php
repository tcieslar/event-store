<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\EventPublisher\EventPublisherInterface;
use Tcieslar\EventStore\EventPublisher\NullEventPublisher;
use PHPUnit\Framework\TestCase;

class NullEventPublisherTest extends TestCase
{
    public function testEventPublisher(): void
    {
        $eventPublisher = new NullEventPublisher();
        $eventPublisher->publish(new EventCollection([]));
        $this->assertInstanceOf(EventPublisherInterface::class, $eventPublisher);
    }
}
