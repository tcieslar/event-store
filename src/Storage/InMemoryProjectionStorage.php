<?php

namespace Storage;

use Aggregate\AggregateIdInterface;
use Projection\ViewInterface;

class InMemoryProjectionStorage implements ProjectionStorageInterface
{
    private array $views = [];

    public function getView(string $viewClass, AggregateIdInterface $aggregateId): ?ViewInterface
    {
        return $this->views[$viewClass][$aggregateId->toString()] ?? null;
    }

    public function storeView(ViewInterface $view): void
    {
        $this->views[get_class($view)] ??= [];
        $this->views[get_class($view)][$view->getAggregateId()->toString()] = $view;
    }
}