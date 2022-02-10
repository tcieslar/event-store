<?php

namespace Tcieslar\EventStore\Example\Aggregate;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;

class OrderId implements AggregateIdInterface
{
    public function __construct(
        public readonly string $guid
    )
    {
    }

    public function toString(): string
    {
        return $this->guid;
    }
}