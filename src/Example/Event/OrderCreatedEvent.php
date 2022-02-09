<?php

namespace Example\Event;

use Aggregate\AggregateIdInterface;
use Example\Aggregate\OrderId;

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