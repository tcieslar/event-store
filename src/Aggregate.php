<?php

abstract class Aggregate
{
    protected EventCollection $recordedEvents;

    public static function loadFromEvents(EventCollection $events): static
    {
        $obj = new static();
        foreach ($events as $event) {
            $obj->mutate($event);
        }

        return $obj;
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

    protected function apply(EventInterface $event): void
    {
        $this->recordedEvents->add($event);
        $this->mutate($event);
    }

    public function reply(EventInterface $event): void
    {
        $this->mutate($event);
    }

    protected function mutate(EventInterface $event): void
    {
        $array = explode('\\', get_class($event));
        $name = 'when' . $array[count($array) - 1];
        try{
            $this->$name($event);
        } catch (Error $error) {
            throw new RuntimeException('Event is no supported, or aggregate type mismatch.',
            0, $error);
        }
    }
}