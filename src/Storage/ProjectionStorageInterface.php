<?php

namespace Tcieslar\EventStore\Storage;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Projection\ViewInterface;

interface ProjectionStorageInterface
{
    public function getView(string $viewClass, AggregateIdInterface $aggregateId): ?ViewInterface;

    public function storeView(ViewInterface $view): void;
}