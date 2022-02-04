<?php

namespace Example;

use EventInterface;

class CustomerCreatedEvent implements EventInterface
{
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct(
        public readonly CustomerId $customerId
    )
    {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getType(): string
    {
        return self::class;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}