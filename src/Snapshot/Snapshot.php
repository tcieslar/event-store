<?php

namespace Snapshot;

use Aggregate\Aggregate;
use Aggregate\Version;

class Snapshot
{
    public function __construct(
        public readonly Aggregate $aggregate,
        public readonly Version $version
    )
    {
    }
}