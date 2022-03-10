<?php

namespace Tcieslar\EventStore\EventPublisher;

use Tcieslar\EventSourcing\EventCollection;

class NullEventPublisher implements EventPublisherInterface
{
    public function publish(EventCollection $collection): void
    {
        return ;
    }
}