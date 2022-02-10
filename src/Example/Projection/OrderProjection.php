<?php

namespace Example\Projection;

use Event\EventInterface;
use Projection\ProjectionInterface;
use Projection\ViewInterface;

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

    public function consumeEvent(string $eventType): bool
    {
        return false;
    }
}