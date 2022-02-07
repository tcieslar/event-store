<?php

namespace Utils;

use Aggregate\Aggregate;

class PhpSerializer implements SerializerInterface
{
    public function serialize(Aggregate $aggregate): string
    {
        return serialize($aggregate);
    }

    public function unserialize(string $serializedAggregate): Aggregate
    {
        return unserialize($serializedAggregate);
    }
}