<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\OrderId;

class OrderAddedEvent extends Event
{
    public function __construct(
        public readonly CustomerId $customerId,
        public readonly OrderId $orderId,
        public readonly string $orderDescription
    )
    {
        parent::__construct();
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->customerId;
    }
}