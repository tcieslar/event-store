<?php

namespace Tcieslar\EventStore\Aggregate;

use InvalidArgumentException;

class Version
{
    private int $number;

    private function __construct()
    {
    }

    public static function zero(): self
    {
        return self::number(0);
    }

    public static function number(int $number): self
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Version number error.');
        }
        $version = new self();
        $version->number = $number;
        return $version;
    }

    public function incremented(): self
    {
        $obj = clone $this;
        $obj->number++;

        return $obj;
    }

    public function isHigherThen(self $version): bool
    {
        return $this->number > $version->number;
    }

    public function isEqual(self $version): bool
    {
        return $this->number === $version->number;
    }

    public function toString(): string
    {
        return '' . $this->number;
    }
}