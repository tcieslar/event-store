<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Psr\Log\LoggerInterface;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\EventStoreInterface;
use Tcieslar\EventStore\Store\LoggableEventStore;
use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Tests\Example\Event\CustomerCreatedEvent;

class LoggableEventStoreTest extends TestCase
{
    public function testLoadFromStream(): void
    {
        $customerId = CustomerId::create();
        $aggregateId = $customerId->getUuid();
        $afterVersion = Version::number(1);

        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore->expects($this->once())
            ->method('loadFromStream')
            ->willReturn(
                new EventStream(
                    $aggregateId,
                    new AggregateType(Customer::class),
                    $afterVersion->incremented(),
                    $afterVersion->incremented()->incremented(),
                    new EventCollection(
                        [
                            new CustomerCreatedEvent($customerId)
                        ])
                ));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with($this->anything(),
                $this->callback(function (array $context) {
                    return isset($context['aggregate_id'], $context['aggregate_type'],
                        $context['after_version'], $context['start_version'], $context['end_version'],
                        $context['events_count']);
                })
            );

        $loggableEventStore = new LoggableEventStore($eventStore, $logger);
        $loggableEventStore->loadFromStream($aggregateId, $afterVersion);
    }

    public function testAppendToStream(): void
    {
        $customerId = CustomerId::create();
        $aggregateId = $customerId->getUuid();
        $aggregateType = new AggregateType(Customer::class);
        $startVersion = Version::number(1);

        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore->expects($this->once())
            ->method('appendToStream');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with($this->anything(),
                $this->callback(function (array $context) {
                    return isset($context['aggregate_id'], $context['aggregate_type'],
                        $context['expected_version'],
                        $context['events_count']);
                })
            );

        $loggableEventStore = new LoggableEventStore($eventStore, $logger);
        $loggableEventStore->appendToStream($aggregateId, $aggregateType, $startVersion, new EventCollection([]));
    }

}
