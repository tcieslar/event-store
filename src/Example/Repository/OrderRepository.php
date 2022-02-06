<?php

namespace Example\Repository;

use Aggregate;
use EventStream;;
use Example\Aggregate\Order;
use Example\Aggregate\OrderId;
use Repository;

class OrderRepository extends Repository
{
    public function find(OrderId $orderId): ?Order
    {
        return $this->findAggregate($orderId);
    }

    protected function createAggregateByEventStream(EventStream $eventStream): Aggregate
    {
        return Order::loadFromEvents($eventStream->events);
    }
}