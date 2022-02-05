<?php

abstract class AbstractSnapshotRepository implements SnapshotRepositoryInterface
{
    protected function __construct(
        protected SerializerInterface $serializer
    )
    {
    }
}