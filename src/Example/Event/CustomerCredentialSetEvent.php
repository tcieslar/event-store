<?php

namespace Example\Event;

use Aggregate\AggregateIdInterface;
use Example\Aggregate\CustomerId;

class CustomerCredentialSetEvent extends Event
{
    public function __construct(
        public readonly CustomerId $customerId,
        public readonly string $name
    )
    {
        parent::__construct();
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->customerId;
    }
}