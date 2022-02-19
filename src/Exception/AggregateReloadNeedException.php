<?php

namespace Tcieslar\EventStore\Exception;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;

class AggregateReloadNeedException extends \Exception
{
    public function __construct(
        public readonly AggregateIdInterface $aggregateId
    )
    {
        parent::__construct();
    }
}