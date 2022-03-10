<?php

namespace Tcieslar\EventStore\Example\Aggregate;

use Tcieslar\EventSourcing\Uuid;

class OrderId
{
    private Uuid $uuid;

    public function __construct(
        ?string $value = null
    )
    {
        if (!$value) {
            $this->uuid = Uuid::random();
        } else {
            $this->uuid = new Uuid($value);
        }
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }

    public static function create(): self
    {
        return new self();
    }

    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }
}