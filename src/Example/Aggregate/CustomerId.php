<?php

namespace Example\Aggregate;

use Aggregate\AggregateIdInterface;

class CustomerId implements AggregateIdInterface
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