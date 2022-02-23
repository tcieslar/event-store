<?php

namespace Tcieslar\EventStore\Example\Aggregate;

use Tcieslar\EventStore\Aggregate\Aggregate;
use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use DateTimeImmutable;
use Tcieslar\EventStore\Example\Event\OrderCreatedEvent;

class Order extends Aggregate
{
    private OrderId $orderId;
    private string $description;
    private DateTimeImmutable $createdAt;

    public static function create(OrderId $orderId, string $description): self
    {
        $obj = new Order();
        $obj->apply(
            new OrderCreatedEvent(
                $orderId,
                $description
            )
        );
        return $obj;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;

    }

    public function getId(): AggregateIdInterface
    {
        return $this->orderId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    protected function whenOrderCreatedEvent(OrderCreatedEvent $event): void
    {
        $this->orderId = $event->getOrderId();
        $this->description = $event->getDescription();
        $this->createdAt = $event->getOccurredAt();
    }
}