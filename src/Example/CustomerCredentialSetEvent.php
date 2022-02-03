<?php

namespace Example;

use EventInterface;

class CustomerCredentialSetEvent implements EventInterface
{
    public function __construct(
        public readonly string $name
    )
    {
    }
}