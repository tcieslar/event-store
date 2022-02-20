<?php

namespace Tcieslar\EventStore\Example\Projection;

use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Event\EventType;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Example\Event\OrderAddedEvent;
use Tcieslar\EventStore\Projection\ProjectionInterface;
use Tcieslar\EventStore\Projection\ViewInterface;

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

        if ($event instanceof OrderAddedEvent) {
            $view->orders ??= [];
            $view->orders[] = $event->orderDescription;
            return $view;
        }
    }

    public function getViewClass(): string
    {
        return CustomerView::class;
    }

    public function consumeEvent(EventType $eventType): bool
    {
        return in_array($eventType,
            [
                EventType::byEventClass(CustomerCreatedEvent::class),
                EventType::byEventClass(CustomerCredentialSetEvent::class),
                EventType::byEventClass(OrderAddedEvent::class
                )]);
    }
}