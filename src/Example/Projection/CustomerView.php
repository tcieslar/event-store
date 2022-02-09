<?php
namespace  Example\Projection;
use Aggregate\AggregateIdInterface;
use DateTimeImmutable;
use Example\Aggregate\CustomerId;
use Projection\ViewInterface;

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