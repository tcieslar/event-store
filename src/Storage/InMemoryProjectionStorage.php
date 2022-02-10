<?php

namespace Tcieslar\EventStore\Storage;

use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Projection\ViewInterface;

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