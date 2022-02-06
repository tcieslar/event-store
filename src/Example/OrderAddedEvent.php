<?php

namespace Example;

use Event;

class OrderAddedEvent extends Event
{
    public function __construct(
        public readonly Order $order
    )
    {
        parent::__construct();
    }
}