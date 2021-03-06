<?php

namespace Tcieslar\EventStore\Example\Aggregate;

use InvalidArgumentException;
use Tcieslar\EventSourcing\Uuid;
use Tcieslar\EventStore\Aggregate\AbstractAggregate;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Example\Event\OrderAddedEvent;

class Customer extends AbstractAggregate
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

    public function getId(): Uuid
    {
        return $this->customerId->getUuid();
    }

    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    public function setName(string $name): void
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Name is empty.');
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
                $order->getOrderId(),
                $order->getDescription()
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
        $this->customerId = $event->getCustomerId();
    }

    protected function whenCustomerCredentialSetEvent(CustomerCredentialSetEvent $event): void
    {
        $this->name = $event->getName();
    }

    protected function whenOrderAddedEvent(OrderAddedEvent $event): void
    {
        $this->orderIds[] = $event->getOrderId();
    }
}