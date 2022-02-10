<?php

namespace Tcieslar\EventStore\ConcurrencyResolving;

use Tcieslar\EventStore\Exception\ConcurrencyException;

class SoftResolvingStrategy implements ConcurrencyResolvingStrategyInterface
{
    public function resolve(ConcurrencyException $exception): void
    {
        // TODO: Implement resolve() method.
    }
}