<?php

namespace Tcieslar\EventStore\ConcurrencyResolving;

use Tcieslar\EventStore\Exception\ConcurrencyException;

interface ConcurrencyResolvingStrategyInterface
{
    public function resolve(ConcurrencyException $exception): void;
}