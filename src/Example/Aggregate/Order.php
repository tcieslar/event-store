<?php

namespace Example\Aggregate;

use Aggregate;
use AggregateIdInterface;
use DateTimeImmutable;
use Example\Event\OrderCreatedEvent;

class Order extends Aggregate
{
    private OrderId $orderId;
    private string $description;
    private DateTimeImmutable $createdAt;

    public function __construct(OrderId $orderId, string $description)
    {
        parent::__construct();

        $this->apply(
           new OrderCreatedEvent(
               $orderId,
               $description
           )
       );
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;

    }

    public function getId(): AggregateIdInterface
    {
        return $this->orderId;
    }

    protected function whenOrderCreatedEvent(OrderCreatedEvent $event): void
    {
        $this->orderId = $event->orderId;
        $this->description = $event->description;
        $this->createdAt = $event->occurredAt;
    }
}