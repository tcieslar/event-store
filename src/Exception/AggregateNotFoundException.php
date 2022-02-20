<?php

namespace Tcieslar\EventStore\Exception;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;

class AggregateNotFoundException extends \Exception
{
    public function __construct(
        public readonly AggregateIdInterface $aggregateId)
    {
        parent::__construct('Aggregate not found.');
    }
}