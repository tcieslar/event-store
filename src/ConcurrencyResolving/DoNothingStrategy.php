<?php

namespace ConcurrencyResolving;

use Exception\ConcurrencyException;
use RuntimeException;

class DoNothingStrategy implements ConcurrencyResolvingStrategyInterface
{
    public function resolve(ConcurrencyException $exception): never
    {
        throw new RuntimeException('Silence, do nothing.');
    }
}