<?php

namespace Unit;

use ConcurrencyResolving\DoNothingStrategy;
use Event\EventCollection;
use Example\Aggregate\CustomerId;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Aggregate\Version;

class DoNothingStrategyTest extends TestCase
{
    public function testHandle(): void
    {
        $exception = new \Exception\ConcurrencyException(
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