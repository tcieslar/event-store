<?php

namespace EventPublisher;

use Event\EventCollection;

interface EventPublisherInterface
{
    public function publish(EventCollection $collection): void;
}