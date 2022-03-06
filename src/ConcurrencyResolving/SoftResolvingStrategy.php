<?php
declare(strict_types=1);

namespace Tcieslar\EventStore\ConcurrencyResolving;

use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventSourcing\Event;
use Tcieslar\EventStore\EventStoreInterface;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventStore\Exception\RealConcurrencyException;

class SoftResolvingStrategy implements ConcurrencyResolvingStrategyInterface
{
    public function __construct(private EventStoreInterface $eventStore)
    {
    }

    /**
     * @throws ConcurrencyException
     * @throws RealConcurrencyException
     */
    public function resolve(ConcurrencyException $exception): void
    {
        $newEvents = new EventCollection();
        /** @var Event $event */
        foreach ($exception->eventsToStore as $event) {
            /** @var Event $storedEvent */
            $store = true;
            foreach ($exception->storedEvents as $storedEvent) {
                if (get_class($storedEvent) === get_class($event)) {
                    $store = false;
                    break;
                }
            }

            if ($store) {
                $newEvents->add($event);
            } else {
                throw new RealConcurrencyException();
            }
        }

        $this->eventStore->appendToStream(
            $exception->aggregateId,
            $exception->aggregateType,
            $exception->actualVersion,
            $newEvents
        );
    }
}