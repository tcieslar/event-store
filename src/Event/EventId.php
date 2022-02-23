<?php

namespace Tcieslar\EventStore\Event;

use Tcieslar\EventStore\Utils\Uuid;

class EventId
{
    private Uuid $uuid;

    public function __construct(
        ?string $uuid = null
    )
    {
        if (!$uuid) {
            $this->uuid = Uuid::random();
        } else {
            $this->uuid = new Uuid($uuid);
        }
    }

    public static function create(): self
    {
        return new self();
    }

    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }
}