<?php

namespace Example\Event;

use Example\Aggregate\CustomerId;

class CustomerCreatedEvent extends Event
{
    public function __construct(
        public readonly CustomerId $orderId
    )
    {
        parent::__construct();
    }
}