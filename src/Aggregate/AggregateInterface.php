<?php

namespace Aggregate;

use Event\EventCollection;
use Event\EventInterface;
use Exception\EventAggregateMismatchException;

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