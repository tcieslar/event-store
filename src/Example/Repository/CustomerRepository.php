<?php

namespace Tcieslar\EventStore\Example\Repository;

use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Aggregate\Repository;
use Tcieslar\EventStore\Exception\AggregateNotFoundException;

class CustomerRepository extends Repository
{
    /**
     * @throws AggregateNotFoundException
     */
    public function find(CustomerId $customerId): Customer
    {
        return $this->findOne($customerId->getUuid());
    }
}