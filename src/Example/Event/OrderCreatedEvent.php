<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Utils\Uuid;
use Tcieslar\EventStore\Example\Aggregate\OrderId;

class OrderCreatedEvent extends DomainEventExample
{
    public function __construct(
        private OrderId     $orderId,
        private string      $description,
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
        return $this->orderId;
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function normalize(): array
    {
        return [
            'order_id' => $this->getAggregateId()->toUuidString(),
            'description' => $this->getDescription(),
            'event_id' => $this->uuid->toString(),
            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
        ];
    }

    public static function denormalize(array $data): static
    {
        return new self(
            OrderId::fromString($data['order_id']),
            $data['description'],
            Uuid::fromString($data['event_id']),
            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
        );
    }


    public function getDescription(): string
    {
        return $this->description;
    }


}