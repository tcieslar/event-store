<?php

namespace Example;

use Repository;

class CustomerRepository extends Repository
{
    public function add(Customer $customer): void
    {
        $this->addAggregate($customer);
    }
}