<?php

namespace Example;

use Event;

class CustomerCreatedEvent extends Event
{
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct(
        public readonly CustomerId $customerId
    )
    {
        parent::__construct();
    }
}