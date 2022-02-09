<?php

namespace Storage;

use Aggregate\AggregateIdInterface;
use Projection\ViewInterface;

interface ProjectionStorageInterface
{
    public function getView(string $viewClass, AggregateIdInterface $aggregateId): ?ViewInterface;

    public function storeView(ViewInterface $view): void;
}