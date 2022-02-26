<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Event\EventId;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;

class CustomerCredentialSetEvent extends DomainEventExample
{
    public function __construct(
        private CustomerId  $customerId,
        private string      $name,
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

    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function normalize(): array
    {
        return [
            'customer_id' => $this->getCustomerId()->toString(),
            'name' => $this->name,
            'event_id' => $this->eventId->toString(),
            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
        ];
    }

    public static function denormalize(array $data): static
    {
        return new self(
            CustomerId::fromString($data['customer_id']),
            $data['name'],
            EventId::fromString($data['event_id']),
            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
        );
    }
}