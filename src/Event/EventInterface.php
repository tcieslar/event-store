<?php

namespace Tcieslar\EventStore\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use DateTimeImmutable;

interface EventInterface
{
    public function getAggregateId(): AggregateIdInterface;

    public function getEventClass(): string;

    public function getOccurredAt(): DateTimeImmutable;
}