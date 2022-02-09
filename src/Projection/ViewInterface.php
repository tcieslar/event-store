<?php

namespace Projection;

use Aggregate\AggregateIdInterface;

interface ViewInterface
{
    public function getAggregateId(): AggregateIdInterface;
}