<?php

class Version
{
    private int $number;

    public function __construct(
    )
    {
    }

    public static function createFirstVersion(): self
    {
        $version = new self();
        $version->number = 0;
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
        return ''.$this->number;
    }
}