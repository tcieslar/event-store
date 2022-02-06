<?php

interface EventPublisherInterface
{
    public function publish(EventCollection $collection): void;
}