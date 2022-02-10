<?php

namespace Tcieslar\EventStore\Snapshot;

use Tcieslar\EventStore\Utils\SerializerInterface;

abstract class AbstractSnapshotRepository implements SnapshotRepositoryInterface
{
    protected function __construct(
        protected SerializerInterface $serializer
    )
    {
    }
}