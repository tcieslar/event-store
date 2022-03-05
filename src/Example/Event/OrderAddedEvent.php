<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Utils\Uuid;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\OrderId;

class OrderAddedEvent extends DomainEventExample
{
    public function __construct(
        private CustomerId  $customerId,
        private OrderId     $orderId,
        private string      $orderDescription,
        ?Uuid               $uuid = null,
        ?\DateTimeImmutable $occurredAt = null
    )
    {
        parent::__construct(
            $uuid,
            $occurredAt
        );
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->customerId;
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function getOrderDescription(): string
    {
        return $this->orderDescription;
    }

    public function normalize(): array
    {
        return [
            'customer_id' => $this->getAggregateId()->toUuidString(),
            'order_id' => $this->getOrderId()->toUuidString(),
            'description' => $this->getOrderDescription(),
            'event_id' => $this->uuid->toString(),
            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
        ];
    }

    public static function denormalize(array $data): static
    {
        return new self(
            CustomerId::fromString($data['customer_id']),
            OrderId::fromString($data['order_id']),
            $data['description'],
            Uuid::fromString($data['event_id']),
            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
        );
    }


}