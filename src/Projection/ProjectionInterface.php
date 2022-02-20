<?php

namespace Tcieslar\EventStore\Projection;

use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Event\EventType;

interface ProjectionInterface
{
    public function projectView(?ViewInterface $view, EventInterface $event): ViewInterface;

    public function getViewClass(): string;

    public function consumeEvent(EventType $eventType): bool;
}