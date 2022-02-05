<?php

class Snapshot
{
    public function __construct(
        public readonly Aggregate $aggregate,
        public readonly  Version $version
    )
    {
    }
}