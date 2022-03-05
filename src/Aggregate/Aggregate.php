<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Exception\EventAggregateMismatchException;
use Tcieslar\EventStore\Utils\Uuid;

interface Aggregate
{
    public function getId(): Uuid;

    public function recordedEvents(): EventCollection;

    public function removeRecordedEvents(): void;

    /**
     * @throws EventAggregateMismatchException
     */
    public function reply(EventInterface $event): void;

    /**
     * @throws EventAggregateMismatchException
     */
    public static function loadFromEvents(EventCollection $events): static;
}