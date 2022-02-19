<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\ConcurrencyResolving\DoNothingStrategy;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\Version;

class DoNothingStrategyTest extends TestCase
{
    public function testHandle(): void
    {
        $exception = new ConcurrencyException(
            new CustomerId(Uuid::v4()),
            Version::createVersion(123),
            Version::createVersion(122),
            new EventCollection(),
            new EventCollection()
        );
        $strategy = new DoNothingStrategy();
        $this->expectException(RuntimeException::class);
        $strategy->resolve($exception);
    }
}