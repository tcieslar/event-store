<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;

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