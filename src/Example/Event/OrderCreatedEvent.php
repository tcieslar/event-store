<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Example\Aggregate\OrderId;

class OrderCreatedEvent extends Event
{
    public function __construct(
        public readonly OrderId $orderId,
        public readonly string $description
    )
    {
        parent::__construct();
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->orderId;
    }
}