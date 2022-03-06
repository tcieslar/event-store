<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Aggregate;

use Error;
use Tcieslar\EventSourcing\Aggregate;
use Tcieslar\EventSourcing\Uuid;
use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventSourcing\Event;
use Tcieslar\EventSourcing\EventAggregateMismatchException;

abstract class AbstractAggregate implements Aggregate
{
    protected EventCollection $recordedEvents;

    /**
     * @throws EventAggregateMismatchException
     */
    public static function loadFromEvents(EventCollection $events): static
    {
        $aggregate = new static();
        foreach ($events as $event) {
            $aggregate->mutate($event);
        }

        return $aggregate;
    }

    protected function __construct()
    {
        $this->recordedEvents = new EventCollection();
    }

    abstract public function getId(): Uuid;

    public function recordedEvents(): EventCollection
    {
        return $this->recordedEvents;
    }

    public function removeRecordedEvents(): void
    {
        $this->recordedEvents = new EventCollection();
    }

    /**
     * @throws EventAggregateMismatchException
     */
    protected function apply(Event $event): void
    {
        $this->recordedEvents->add($event);
        $this->mutate($event);
    }

    /**
     * @throws EventAggregateMismatchException
     */
    public function reply(Event $event): void
    {
        $this->mutate($event);
    }

    /**
     * @throws EventAggregateMismatchException
     */
    protected function mutate(Event $event): void
    {
        $array = explode('\\', get_class($event));
        $name = 'when' . $array[count($array) - 1];
        try {
            $this->$name($event);
        } catch (Error $error) {
            throw new EventAggregateMismatchException($error, $event);
        }
    }
}