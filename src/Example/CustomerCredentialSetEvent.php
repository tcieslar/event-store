<?php

namespace Example;

use Event;

class CustomerCredentialSetEvent extends Event
{
    public function __construct(
        public readonly string $name
    )
    {
        parent::__construct();
    }
}