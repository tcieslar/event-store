<?php

namespace Tcieslar\EventStore\Utils;

use Symfony\Component\Uid\Uuid as SymfonyUuid;

class Uuid
{
    public function __construct(
        private string $uuid
    )
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1) {
            throw new \InvalidArgumentException('Uuid wrong value.');
        }
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