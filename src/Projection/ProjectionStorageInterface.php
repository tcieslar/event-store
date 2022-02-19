<?php

namespace Tcieslar\EventStore\Projection;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;

interface ProjectionStorageInterface
{
    public function getView(string $viewClass, AggregateIdInterface $aggregateId): ?ViewInterface;

    public function storeView(ViewInterface $view): void;
}