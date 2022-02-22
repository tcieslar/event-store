<?php

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Aggregate\AggregateInterface;
use Tcieslar\EventStore\Aggregate\Version;

class Snapshot
{
    public function __construct(
        public readonly AggregateInterface $aggregate,
        public readonly Version $endVersion
    )
    {
    }
}