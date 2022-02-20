<?php

namespace Tcieslar\EventStore\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Store\DbalEventStore;

/**
 * @group integration
 */
class DbalEventStoreTest extends TestCase
{
    public function testAppendToStream(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'name 1');

        $db = new DbalEventStore();
        $db->appendToStream($customer->getId(),
            $customer->getType(),
            Version::zero(),
            $customer->recordedEvents()
        );

        $eventStream = $db->loadFromStream($customerId);

        $this->assertEquals($customerId, $eventStream->aggregateId);
        $this->assertEquals(Version::number(1), $eventStream->startVersion);
        $this->assertEquals(Version::number(2), $eventStream->endVersion);
        $this->assertEquals($customer->getType(), $eventStream->aggregateType);
        $this->assertCount(2, $eventStream->events);

        $eventStream = $db->loadFromStream($customerId, Version::zero());
        $this->assertCount(2, $eventStream->events);

        $eventStream = $db->loadFromStream($customerId, Version::number(1));
        $this->assertCount(1, $eventStream->events);
    }
}