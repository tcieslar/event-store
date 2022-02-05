<?php

namespace Example;

use Aggregate;
use EventStream;
use Repository;

class CustomerRepository extends Repository
{
    public function find(CustomerId $customerId): ?Customer
    {
        return $this->findAggregate($customerId);
    }

    protected function createAggregateByEventStream(EventStream $eventStream): Aggregate
    {
        return Customer::loadFromEvents($eventStream->events);
    }
}