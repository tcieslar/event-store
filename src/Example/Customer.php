<?php

namespace Example;

use Aggregate;

class Customer extends Aggregate
{
    public static function create(CustomerId $customerId, string $name): self
    {
        return new Customer($customerId, $name);
    }

    private function __construct(
        private CustomerId $customerId,
        private string $name,
    )
    {
        $this->apply(
            new CustomerCreatedEvent($customerId)
        );

        $this->apply(
            new CustomerCredentialSetEvent($name)
        );
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