<?php

namespace Example\Event;

use Example\Aggregate\CustomerId;

class CustomerCredentialSetEvent extends Event
{
    public function __construct(
        public readonly CustomerId $orderId,
        public readonly string $name
    )
    {
        parent::__construct();
    }
}