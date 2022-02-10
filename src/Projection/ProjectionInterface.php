<?php

namespace Tcieslar\EventStore\Projection;

use Tcieslar\EventStore\Event\EventInterface;

interface ProjectionInterface
{
    public function projectView(?ViewInterface $view, EventInterface $event): ViewInterface;

    public function getViewClass(): string;

    public function consumeEvent(string $eventType): bool;
}