<?php

namespace Projection;

use Event\EventCollection;
use Event\EventInterface;
use Storage\ProjectionStorageInterface;

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
            if (!$projection->consumeEvent($event->getEventClass())) {
                continue;
            }

            $view = $this->projectionStorage->getView($projection->getViewClass(), $event->getAggregateId());
            $newView = $projection->projectView($view, $event);
            $this->projectionStorage->storeView($newView);
        }
    }
}