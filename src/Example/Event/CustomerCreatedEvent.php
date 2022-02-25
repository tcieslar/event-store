<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Event\EventId;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;

class CustomerCreatedEvent extends DomainEventExample
{
    public function __construct(
        private CustomerId  $customerId,
        ?EventId            $eventId = null,
        ?\DateTimeImmutable $occurredAt = null
    )
    {
        parent::__construct(
            $eventId,
            $occurredAt
        );
    }

    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->customerId;
    }

    public function normalize(): array
    {
        return [
            'customer_id' => $this->getCustomerId()->toString(),
            'event_id' => $this->eventId->toString(),
            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
        ];
    }

    public static function denormalize(array $data): static
    {
        return new self(
            new CustomerId($data['customer_id']),
            EventId::fromString($data['event_id']),
            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
        );
    }
}