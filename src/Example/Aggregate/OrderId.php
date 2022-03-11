<?php

namespace Tcieslar\EventStore\Example\Aggregate;

use Tcieslar\EventSourcing\Uuid;

class OrderId
{
    private Uuid $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::random();
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
}