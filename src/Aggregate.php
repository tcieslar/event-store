<?php

abstract class Aggregate
{
    protected EventCollection $changes;

    public static function loadFromEvents(EventCollection $events): self
    {
        $obj = new static();
        foreach ($events as $event) {
            $obj->mutate($event);
        }

        return $obj;
    }

    public function __construct()
    {
        $this->changes = new EventCollection();
    }

    abstract public function getId(): AggregateIdInterface;

    public function getChanges(): EventCollection
    {
        return $this->changes;
    }

    public function removeChanges(): void
    {
        $this->changes = new EventCollection();
    }

    protected function apply(EventInterface $event): void
    {
        $this->changes->add($event);
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
        $this->$name($event);
    }
}