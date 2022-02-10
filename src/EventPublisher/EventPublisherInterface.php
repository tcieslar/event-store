<?php

namespace Tcieslar\EventStore\EventPublisher;

use Tcieslar\EventStore\Event\EventCollection;

interface EventPublisherInterface
{
    public function publish(EventCollection $collection): void;
}