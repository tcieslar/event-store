<?php

namespace Utils;

use Aggregate\AggregateInterface;

interface SerializerInterface
{
    public function serialize(AggregateInterface $aggregate): string;

    public function unserialize(string $serializedAggregate): AggregateInterface;
}