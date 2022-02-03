<?php

interface EventStoreInterface
{
    public function loadEventStream(IdentityInterface $identity): EventStream;

    public function appendToStream(IdentityInterface $identity, int $expectedVersion, array $events): void;
}