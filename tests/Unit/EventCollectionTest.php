<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use PHPUnit\Framework\TestCase;

class EventCollectionTest extends TestCase
{
    public function testIterator(): void
    {
        $collection = new EventCollection();
        $collection->add(new CustomerCreatedEvent(CustomerId::create()));
        $collection->add(new CustomerCredentialSetEvent(CustomerId::create(), 'test 1'));

        foreach ($collection as $key => $item) {
            $this->assertGreaterThanOrEqual(0, $key);
            $this->assertNotNull($item);
        }
    }
}