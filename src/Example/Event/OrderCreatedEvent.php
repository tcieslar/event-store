<?php

namespace Example\Event;

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
}