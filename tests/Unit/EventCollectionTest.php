<?php

namespace Unit;

use Event\EventCollection;
use Example\Aggregate\CustomerId;
use Example\Event\CustomerCreatedEvent;
use Example\Event\CustomerCredentialSetEvent;
use PHPUnit\Framework\TestCase;

class EventCollectionTest extends TestCase
{
    public function testIterator(): void
    {
        $collection = new EventCollection();
        $collection->add(new CustomerCreatedEvent(new CustomerId('guid')));
        $collection->add(new CustomerCredentialSetEvent(new CustomerId('guid'), 'test 1'));

        foreach ($collection as $key => $item) {
            $this->assertGreaterThanOrEqual(0, $key);
            $this->assertNotNull($item);
        }
    }
}