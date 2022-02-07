<?php

namespace Unit;

use DoNothingStrategy;
use EventCollection;
use Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Version;

class DoNothingStrategyTest extends TestCase
{
    public function testHandle(): void
    {
        $exception = new \ConcurrencyException(
            new CustomerId(Uuid::v4()),
            Version::createVersion(123),
            new EventCollection(),
            new EventCollection()
        );
        $strategy = new DoNothingStrategy();
        $this->expectException(RuntimeException::class);
        $strategy->resolve($exception);
    }
}