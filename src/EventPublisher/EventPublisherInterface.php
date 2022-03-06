<?php

namespace Tcieslar\EventStore\EventPublisher;

use Tcieslar\EventSourcing\EventCollection;

interface EventPublisherInterface
{
    public function publish(EventCollection $collection): void;
}