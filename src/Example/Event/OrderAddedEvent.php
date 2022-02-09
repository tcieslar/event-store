<?php

namespace Example\Event;

use Aggregate\AggregateIdInterface;
use Example\Aggregate\CustomerId;
use Example\Event\Event;
use Example\Aggregate\OrderId;

class OrderAddedEvent extends Event
{
    public function __construct(
        public readonly CustomerId $customerId,
        public readonly OrderId $orderId
    )
    {
        parent::__construct();
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->customerId;
    }
}