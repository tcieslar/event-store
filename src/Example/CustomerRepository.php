<?php

namespace Example;

use Aggregate;
use EventStream;
use Repository;

class CustomerRepository extends Repository
{
    protected function createAggregateByEventStream(EventStream $eventStream): Aggregate
    {
        return Customer::loadFromEvents($eventStream->events);
    }
}