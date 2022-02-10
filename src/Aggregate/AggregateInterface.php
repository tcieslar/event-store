<?php

namespace Tcieslar\EventStore\Aggregate;

use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Exception\EventAggregateMismatchException;

interface AggregateInterface
{
    /**
     * @throws EventAggregateMismatchException
     */
    public static function loadFromEvents(EventCollection $events): static;

    public function getId(): AggregateIdInterface;

    public function recordedEvents(): EventCollection;

    public function removeRecordedEvents(): void;

    /**
     * @throws EventAggregateMismatchException
     */
    public function reply(EventInterface $event): void;
}