<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventSourcing\Uuid;

class CustomerCreatedEvent extends DomainEventExample
{
    public function __construct(
        private CustomerId  $customerId,
        ?Uuid               $uuid = null,
        ?\DateTimeImmutable $occurredAt = null
    )
    {
        parent::__construct(
            $uuid,
            $occurredAt
        );
    }

    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }


//    public function normalize(): array
//    {
//        return [
//            'customer_id' => $this->getCustomerId()->toString(),
//            'event_id' => $this->uuid->toString(),
//            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
//        ];
//    }
//
//    public static function denormalize(array $data): static
//    {
//        return new self(
//            CustomerId::fromString($data['customer_id']),
//            Uuid::fromString($data['event_id']),
//            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
//        );
//    }
}