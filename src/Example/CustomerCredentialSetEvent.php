<?php

namespace Example;

use EventInterface;

class CustomerCredentialSetEvent implements EventInterface
{
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct(
        public readonly string $name
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