<?php

namespace ConcurrencyResolving;

use Exception\ConcurrencyException;

class SoftResolvingStrategy implements ConcurrencyResolvingStrategyInterface
{
    public function resolve(ConcurrencyException $exception): void
    {
        // TODO: Implement resolve() method.
    }
}