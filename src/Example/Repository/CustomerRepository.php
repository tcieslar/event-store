<?php

namespace Example\Repository;

use Example\Aggregate\Customer;
use Example\Aggregate\CustomerId;
use Aggregate\Repository;

class CustomerRepository extends Repository
{
    public function find(CustomerId $customerId): ?Customer
    {
        return $this->findOne($customerId);
    }

    protected static function getAggregateClassName(): string
    {
        return Customer::class;
    }
}