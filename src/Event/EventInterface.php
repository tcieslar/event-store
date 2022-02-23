<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Event;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use DateTimeImmutable;

interface EventInterface
{
    public function getEventId(): EventId;

    public function getAggregateId(): AggregateIdInterface;

    public function getOccurredAt(): DateTimeImmutable;
}