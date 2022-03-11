<?php

namespace Tcieslar\EventStore\Exception;

class AggregateReloadNeedException extends \Exception
{
    public function __construct(
        public readonly array $aggregatesIds
    )
    {
        parent::__construct();
    }
}