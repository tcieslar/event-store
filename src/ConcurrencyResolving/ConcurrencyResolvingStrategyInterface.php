<?php

namespace ConcurrencyResolving;

use Exception\ConcurrencyException;

interface ConcurrencyResolvingStrategyInterface
{
    public function resolve(ConcurrencyException $exception): void;
}