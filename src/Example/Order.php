<?php

namespace Example;

class Order
{
    public function __construct(
        private OrderId $orderId,
        private string             $description,
        private \DateTimeImmutable $createdAt)
    {
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}