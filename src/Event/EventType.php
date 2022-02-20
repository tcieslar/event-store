<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Event;

use JetBrains\PhpStorm\Pure;

class EventType
{
    public function __construct(
        private string $classFqcn
    )
    {
    }

    #[Pure] public static function byEvent(EventInterface $event): self
    {
        return new self(get_class($event));
    }

    public static function byEventClass(string $className): self
    {
        return new self($className);
    }

    public function getTypeName(): string
    {
        $array = explode('\\', $this->classFqcn);
        return $array[count($array) - 1];
    }

    public function equals(self $eventType): bool
    {
        return $eventType->classFqcn === $this->classFqcn;
    }

    public function toString(): string
    {
        return $this->classFqcn;
    }
}