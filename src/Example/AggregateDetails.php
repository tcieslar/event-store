<?php

class AggregateDetails
{
    public function __construct(
        readonly Version $version,
        readonly string $type
    )
    {
    }
}