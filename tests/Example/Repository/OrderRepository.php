<?php

namespace Tcieslar\EventStore\Tests\Example\Repository;

use Tcieslar\EventStore\Tests\Example\Aggregate\Order;
use Tcieslar\EventStore\Tests\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Aggregate\Repository;

class OrderRepository extends Repository
{
    public function find(OrderId $customerId): ?Order
    {
        return $this->findAggregate($customerId->getUuid());
    }
}