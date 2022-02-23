<?php

namespace Tcieslar\EventStore\Utils;

use Symfony\Component\Uid\Uuid as SymfonyUuid;

class Uuid
{
    public function __construct(
        private string $uuid
    )
    {
    }

    public static function random(): self
    {
        return new self(SymfonyUuid::v4()->toRfc4122());
    }

    public static function fromString(string $uuid): self
    {
        return new self($uuid);
    }

    public function toString(): string
    {
        return $this->uuid;
    }
}