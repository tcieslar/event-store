<?php

namespace Tcieslar\EventStore\Aggregate;

interface AggregateIdInterface
{
    public function toString(): string;
}