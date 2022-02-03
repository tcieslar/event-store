<?php

namespace Example;

class CustomerCreatedEvent implements \EventInterface
{
    public function __construct(
        public readonly CustomerId $customerId
    )
    {
    }
}