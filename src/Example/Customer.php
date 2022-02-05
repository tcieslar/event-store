<?php

namespace Example;

use Aggregate;

class Customer extends Aggregate
{
    private CustomerId $customerId;
    private string $name;

    public static function create(CustomerId $customerId, string $name): self
    {
        $customer = new Customer();
        $customer->apply(
            new CustomerCreatedEvent($customerId)
        );
        $customer->apply(
            new CustomerCredentialSetEvent($name)
        );

        return $customer;
    }

    protected function __construct(
    )
    {
        parent::__construct();
    }

    public function getId(): CustomerId
    {
        return $this->customerId;
    }

    public function setName(string $name): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Name is empty.');
        }

        $this->apply(
            new CustomerCredentialSetEvent($name)
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function whenCustomerCreatedEvent(CustomerCreatedEvent $event): void
    {
        $this->customerId = $event->customerId;
    }

    protected function whenCustomerCredentialSetEvent(CustomerCredentialSetEvent $event): void
    {
        $this->name = $event->name;
    }
}