<?php

namespace Snapshot;

use Utils\SerializerInterface;

abstract class AbstractSnapshotRepository implements SnapshotRepositoryInterface
{
    protected function __construct(
        protected SerializerInterface $serializer
    )
    {
    }
}