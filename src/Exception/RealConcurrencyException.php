<?php

namespace Tcieslar\EventStore\Exception;

class RealConcurrencyException extends \Exception
{
    public function __construct()
    {
        parent::__construct();
    }
}