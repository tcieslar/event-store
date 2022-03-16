<?php

namespace Tcieslar\EventStore\Tests\Example\Repository;

use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;
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