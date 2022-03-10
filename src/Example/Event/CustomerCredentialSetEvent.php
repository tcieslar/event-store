<?php

namespace Tcieslar\EventStore\Example\Event;

use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventSourcing\Uuid;

class CustomerCredentialSetEvent extends DomainEventExample
{
    public function __construct(
        private CustomerId  $customerId,
        private string      $name,
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

    public function getName(): string
    {
        return $this->name;
    }

//    public function normalize(): array
//    {
//        return [
//            'customer_id' => $this->getCustomerId()->toString(),
//            'name' => $this->name,
//            'event_id' => $this->uuid->toString(),
//            'occurred_at' => $this->occurredAt->format(DATE_RFC3339)
//        ];
//    }
//
//    public static function denormalize(array $data): static
//    {
//        return new self(
//            CustomerId::fromString($data['customer_id']),
//            $data['name'],
//            Uuid::fromString($data['event_id']),
//            \DateTimeImmutable::createFromFormat(DATE_RFC3339, $data['occurred_at'])
//        );
//    }
}