<?php

namespace Example;

use IdentityInterface;

class CustomerId implements IdentityInterface
{
    public function __construct(
        private string $uuid
    )
    {
    }

    public function toString(): string
    {
        return $this->uuid;
    }
}