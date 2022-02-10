<?php

namespace Tcieslar\EventStore\Utils;

use Tcieslar\EventStore\Aggregate\AggregateInterface;

interface SerializerInterface
{
    public function serialize(AggregateInterface $aggregate): string;

    public function unserialize(string $serializedAggregate): AggregateInterface;
}