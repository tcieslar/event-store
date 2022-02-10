<?php

namespace Tcieslar\EventStore\Example\Repository;

use Tcieslar\EventStore\Example\Aggregate\Order;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Aggregate\Repository;

class OrderRepository extends Repository
{
    public function find(OrderId $customerId): ?Order
    {
        return $this->findOne($customerId);
    }

    protected static function getAggregateClassName(): string
    {
        return Order::class;
    }
}