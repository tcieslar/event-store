<?php

namespace Tcieslar\EventStore\Example\Projection;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use DateTimeImmutable;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Projection\ViewInterface;

class CustomerView implements ViewInterface
{
    public function __construct(
        public CustomerId $customerId,
        public ?DateTimeImmutable $createdAt = null,
        public ?string            $name = null,
        public ?array             $orders = null,
    )
    {
    }

    public function getAggregateId(): AggregateIdInterface
    {
        return $this->customerId;
    }
}