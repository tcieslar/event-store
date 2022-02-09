<?php

namespace Example\Event;

use Aggregate\AggregateIdInterface;
use Example\Aggregate\CustomerId;

class CustomerCreatedEvent extends Event
{
    public function __construct(
        public readonly CustomerId $customerId
    )
    {
        parent::__construct();
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->customerId;
    }
}