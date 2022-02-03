<?php

namespace Example;

use Aggregate;
use IdentityInterface;
use Repository;

class CustomerRepository extends Repository
{
    public function add(Customer $customer): void
    {
        $this->persistAggregate($customer);
    }

    public function find(IdentityInterface $identity): ?Customer
    {
        $customer = Customer::loadFromEvents($this->getAggregateEvents($identity));
        $this->persistAggregate($customer);
        return $customer;
    }

    protected function getClassName(): string
    {
        return Customer::class;
    }
}