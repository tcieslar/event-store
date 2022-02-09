<?php

namespace Example\Projection;

use Event\EventInterface;
use Example\Event\CustomerCreatedEvent;
use Example\Event\CustomerCredentialSetEvent;
use Projection\ProjectionInterface;
use Projection\ViewInterface;

class CustomerProjection implements ProjectionInterface
{

    public function projectView(?ViewInterface $view, EventInterface $event): ViewInterface
    {
        if ($event instanceof CustomerCreatedEvent) {
            return new CustomerView($event->customerId, $event->occurredAt);
        }

        if (!$view instanceof CustomerView) {
            return $view;
        }

        if ($event instanceof CustomerCredentialSetEvent) {
            $view->name = $event->name;
            return $view;
        }
    }

    public function getViewClass(): string
    {
        return CustomerView::class;
    }

    public function consumeEvent(string $eventType): bool
    {
        return in_array($eventType,
            [
                CustomerCreatedEvent::class,
                CustomerCredentialSetEvent::class
            ]);
    }
}