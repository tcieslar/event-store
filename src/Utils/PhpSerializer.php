<?php

namespace Utils;

use Aggregate\AggregateInterface;

class PhpSerializer implements SerializerInterface
{
    public function serialize(AggregateInterface $aggregate): string
    {
        return serialize($aggregate);
    }

    public function unserialize(string $serializedAggregate): AggregateInterface
    {
        return unserialize($serializedAggregate);
    }
}