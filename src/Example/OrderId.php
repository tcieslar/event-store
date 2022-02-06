<?php

namespace Example;

class OrderId
{
    public function __construct(
        public readonly string $uuid
    )
    {
    }
}