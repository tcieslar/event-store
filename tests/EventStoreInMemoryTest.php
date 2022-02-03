<?php

use Example\CustomerId;
use Example\EventStoreInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class EventStoreInMemoryTest extends TestCase
{
    public function testLoadEmpty(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $eventStream = new EventStoreInMemory();

        $this->expectExceptionMessage('Aggregate not found.');
        $eventStream->loadEventStream($customerId);
    }

}