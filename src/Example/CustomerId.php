<?php

namespace Example;

use AggregateIdInterface;

class CustomerId implements AggregateIdInterface
{
    public function __construct(
        private string $guid
    )
    {
    }

    public function toString(): string
    {
        return $this->guid;
    }
}