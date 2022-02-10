<?php

namespace Snapshot;

use Aggregate\AggregateInterface;
use Aggregate\Version;

class Snapshot
{
    public function __construct(
        public readonly AggregateInterface $aggregate,
        public readonly Version $version
    )
    {
    }
}