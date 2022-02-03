<?php

abstract class Aggregate
{
    protected array $changes = [];

    /**
     * @param array<EventInterface> $events
     */
    public static function loadFromEventStream(array $events): static
    {
        $obj = new static();
        foreach ($events as $event) {
            $obj->mutate($event);
        }
    }

    abstract public function getId(): IdentityInterface;

    protected function apply(EventInterface $event): void
    {
        $this->changes[] = $event;
        $this->mutate($event);
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    protected function mutate(EventInterface $event): void
    {
        $array = explode('\\', get_class($event));
        $name = 'when' . $array[count($array) - 1];
        $this->$name($event);
    }
}