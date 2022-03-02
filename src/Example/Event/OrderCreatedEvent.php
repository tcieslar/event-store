<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Event\EventId;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\OrderId;

class OrderCreatedEvent extends DomainEventExample
{
    public function __construct(
        private OrderId     $orderId,
        private string      $description,
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
        return $this->orderId;
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function normalize(): array
    {
        return [
            'order_id' => $this->getAggregateId()->toString(),
            'description' => $this->getDescription(),
            'event_id' => $this->eventId->toString(),
            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
        ];
    }

    public static function denormalize(array $data): static
    {
        return new self(
            OrderId::fromString($data['order_id']),
            $data['description'],
            EventId::fromString($data['event_id']),
            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
        );
    }


    public function getDescription(): string
    {
        return $this->description;
    }


}