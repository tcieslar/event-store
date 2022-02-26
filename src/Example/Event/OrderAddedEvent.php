<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Event\EventId;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\OrderId;

class OrderAddedEvent extends DomainEventExample
{
    public function __construct(
        private CustomerId  $customerId,
        private OrderId     $orderId,
        private string      $orderDescription,
        ?EventId            $eventId = null,
        ?\DateTimeImmutable $occurredAt = null
    )
    {
        parent::__construct(
            $eventId,
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
            'customer_id' => $this->getAggregateId()->toString(),
            'order_id' => $this->getOrderId()->toString(),
            'description' => $this->getOrderDescription(),
            'event_id' => $this->eventId->toString(),
            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
        ];
    }

    public static function denormalize(array $data): static
    {
        return new self(
            CustomerId::fromString($data['customer_id']),
            OrderId::fromString($data['order_id']),
            $data['description'],
            EventId::fromString($data['event_id']),
            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
        );
    }


}