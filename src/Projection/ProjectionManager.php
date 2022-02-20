<?php

namespace Tcieslar\EventStore\Projection;

use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Projection\ProjectionStorageInterface;

class ProjectionManager
{
    /**
     * @var array<ProjectionInterface>
     */
    private array $projections;
    private ProjectionStorageInterface $projectionStorage;

    /**
     * @param array<ProjectionInterface> $projections
     */
    public function __construct(ProjectionStorageInterface $projectionStorage, array $projections)
    {
        $this->projections = $projections;
        $this->projectionStorage = $projectionStorage;
    }

    public function projectViewsByEventCollection(EventCollection $eventCollection): void
    {
        foreach ($eventCollection  as $event) {
            $this->projectViews($event);
        }
    }

    public function projectViews(EventInterface $event): void
    {
        foreach ($this->projections as $projection) {
            if (!$projection->consumeEvent($event->getEventType())) {
                continue;
            }

            $view = $this->projectionStorage->getView($projection->getViewClass(), $event->getAggregateId());
            $newView = $projection->projectView($view, $event);
            $this->projectionStorage->storeView($newView);
        }
    }
}