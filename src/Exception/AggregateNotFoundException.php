<?php

namespace Tcieslar\EventStore\Exception;

use Tcieslar\EventStore\Utils\Uuid;

class AggregateNotFoundException extends \Exception
{
    public function __construct(
        public readonly Uuid $aggregateId)
    {
        parent::__construct('Aggregate not found.');
    }
}