<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

interface AggregateIdInterface
{
    public function toUuidString(): string;
}