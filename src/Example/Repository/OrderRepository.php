<?php

namespace Example\Repository;

use Example\Aggregate\Order;
use Example\Aggregate\OrderId;
use Aggregate\Repository;

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