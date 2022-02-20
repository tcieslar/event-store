<?php

namespace Tcieslar\EventStore\Example\Projection;

use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Event\EventType;
use Tcieslar\EventStore\Projection\ProjectionInterface;
use Tcieslar\EventStore\Projection\ViewInterface;

class OrderProjection implements ProjectionInterface
{

    public function projectView(?ViewInterface $view, EventInterface $event): ViewInterface
    {
        // TODO: Implement projectView() method.
    }

    public function getViewClass(): string
    {
        return OrderView::class;
    }

    public function consumeEvent(EventType $eventType): bool
    {
        return false;
    }
}