<?php

namespace Tcieslar\EventStore\Store;

use Tcieslar\EventSourcing\EventCollection;

interface EventProviderInterface
{
    public function getEventsCount(): int;
    public function getEvents(int $page = 1, int $pageLimit = 1000): EventCollection;
}