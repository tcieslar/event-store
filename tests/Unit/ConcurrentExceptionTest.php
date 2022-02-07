<?php

namespace Unit;

use ConcurrencyException;
use EventCollection;
use Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Version;

class ConcurrentExceptionTest extends TestCase
{
    public function testException(): void
    {
        $exception = new ConcurrencyException(
            new CustomerId(Uuid::v4()),
            Version::createVersion(123),
            new EventCollection(),
            new EventCollection()
        );

        $this->assertNotNull($exception->aggregateId);
    }
}