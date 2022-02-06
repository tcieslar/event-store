<?php

interface EventInterface
{
    public function getType(): string;

    public function getOccurredAt(): \DateTimeImmutable;
}