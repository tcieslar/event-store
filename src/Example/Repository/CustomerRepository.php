<?php

namespace Example\Repository;

use Aggregate;
use EventStream;
use Example\Aggregate\Customer;
use Example\Aggregate\CustomerId;
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