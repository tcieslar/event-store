<?php

namespace Tcieslar\EventStore\Event;

class EventCollection implements \Countable, \Iterator
{
    private array $values = [];

    private int $position = 0;


    public function __construct(array $values = [])
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function getAll(): array
    {
        return $this->values;

    }

    public function add(EventInterface $event): void
    {
        $this->values[] = $event;
    }

    public function get(int $position): mixed
    {
        return $this->values[$position];
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): mixed
    {
        return $this->values[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function valid(): bool
    {
        return isset($this->values[$this->position]);
    }
}