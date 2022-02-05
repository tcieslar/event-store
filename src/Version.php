<?php

class Version
{
    private int $number;

    private function __construct()
    {
    }

    public static function createFirstVersion(): self
    {
        return self::createVersion(0);
    }

    public static function createVersion(int $number): self
    {
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
        return ''.$this->number;
    }
}