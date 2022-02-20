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
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'name 1');

        $db = new DbalEventStore();
        $db->appendToStream($customer->getId(),
            $customer->getType(),
            Version::zero(),
            $customer->recordedEvents()
        );
    }

    public function testLoadStream(): void
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
    }

}