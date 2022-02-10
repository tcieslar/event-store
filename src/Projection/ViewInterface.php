<?php

namespace Tcieslar\EventStore\Projection;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;

interface ViewInterface
{
    public function getAggregateId(): AggregateIdInterface;
}