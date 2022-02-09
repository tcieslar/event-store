<?php

namespace Example\Aggregate;

use Aggregate\Aggregate;
use Example\Event\CustomerCreatedEvent;
use Example\Event\CustomerCredentialSetEvent;
use Example\Event\OrderAddedEvent;

class Customer extends Aggregate
{
    private CustomerId $customerId;
    private string $name;
    private array $orderIds;

    public static function create(CustomerId $customerId, string $name): self
    {
        $customer = new Customer();
        $customer->apply(
            new CustomerCreatedEvent($customerId)
        );
        $customer->apply(
            new CustomerCredentialSetEvent($customerId, $name)
        );

        return $customer;
    }

    protected function __construct()
    {
        $this->orderIds = [];
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
            new CustomerCredentialSetEvent($this->customerId, $name)
        );
    }

    public function addOrder(Order $order): void
    {
        $this->apply(
            new OrderAddedEvent(
                $this->customerId,
                $order->getOrderId()
            )
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrderIds(): array
    {
        return $this->orderIds;

    }

    protected function whenCustomerCreatedEvent(CustomerCreatedEvent $event): void
    {
        $this->customerId = $event->customerId;
    }

    protected function whenCustomerCredentialSetEvent(CustomerCredentialSetEvent $event): void
    {
        $this->name = $event->name;
    }

    protected function whenOrderAddedEvent(OrderAddedEvent $event): void
    {
        $this->orderIds[] = $event->orderId;
    }
}