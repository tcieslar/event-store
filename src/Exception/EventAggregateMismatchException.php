<?php

namespace Exception;

use Aggregate\AggregateIdInterface;
use Event\EventInterface;
use Exception;
use Throwable;

class EventAggregateMismatchException extends Exception
{
    public readonly EventInterface $event;
    public readonly AggregateIdInterface $aggregateId;

    public function __construct(Throwable $throwable, EventInterface $event, AggregateIdInterface $aggregateId)
    {
        parent::__construct('Event is no supported, or aggregate type mismatch.', 0, $throwable);
        $this->event = $event;
        $this->aggregateId = $aggregateId;
    }
}