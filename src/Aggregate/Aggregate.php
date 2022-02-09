<?php

namespace Aggregate;

use Error;
use Event\EventCollection;
use Event\EventInterface;
use Exception\EventAggregateMismatchException;

abstract class Aggregate implements AggregateInterface
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

    public function __construct()
    {
        $this->recordedEvents = new EventCollection();
    }

    abstract public function getId(): AggregateIdInterface;

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
    protected function apply(EventInterface $event): void
    {
        $this->recordedEvents->add($event);
        $this->mutate($event);
    }

    /**
     * @throws EventAggregateMismatchException
     */
    public function reply(EventInterface $event): void
    {
        $this->mutate($event);
    }

    /**
     * @throws EventAggregateMismatchException
     */
    protected function mutate(EventInterface $event): void
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