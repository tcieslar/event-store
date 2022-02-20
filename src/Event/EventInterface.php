<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use DateTimeImmutable;

interface EventInterface
{
    public function getAggregateId(): AggregateIdInterface;

    public function getEventType(): EventType;

    public function getOccurredAt(): DateTimeImmutable;
}