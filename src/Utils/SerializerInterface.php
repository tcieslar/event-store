<?php

namespace Utils;

use Aggregate\Aggregate;

interface SerializerInterface
{
    public function serialize(Aggregate $aggregate): string;

    public function unserialize(string $serializedAggregate): Aggregate;
}