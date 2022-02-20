<?php

namespace Tcieslar\EventStore\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Store\DbalEventStore;

/**
 * @group integration
 */
class DbalEventStoreTest extends TestCase
{
    public function testAppendToStream(): void
    {
        $db = new DbalEventStore();
        $aggregateId = new CustomerId(Uuid::v4());
        $db->appendToStream($aggregateId,
            new AggregateType(Customer::class),
            Version::zero(),
            new EventCollection(
                [new CustomerCreatedEvent($aggregateId)]
            ));
    }

    public function testLoadStream(): void
    {
        $db = new DbalEventStore();
        $db->loadFromStream(new CustomerId('4b6acb89-0871-4e40-844d-5345e56753ff'));
    }

}