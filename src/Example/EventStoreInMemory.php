<?php

namespace Example;

use EventStoreInterface;
use EventStream;
use IdentityInterface;

class EventStoreInMemory implements EventStoreInterface
{
    public function __construct(
        private array $events = []
    )
    {
    }

    public function loadEventStream(IdentityInterface $identity): EventStream
    {
        if (!isset($this->events[$identity->toString()])) {
            throw new \InvalidArgumentException('Aggregate not found.');
        }
        return new EventStream(
            version: 1,
            events: $this->events[$identity->toString()]
        );
    }

    public function appendToStream(IdentityInterface $identity, int $expectedVersion, array $events): void
    {
        if (!isset($this->events[$identity->toString()])) {
            $this->events[$identity->toString()] = [];
        }

        foreach ($events as $event) {
            $this->events[$identity->toString()][] = $event;
        }
    }

    public function getAllEvents(): array
    {
        $result = [];
        foreach ($this->events as $eventArray) {
             $result = [...$eventArray];
        }

        return $result;
    }
}