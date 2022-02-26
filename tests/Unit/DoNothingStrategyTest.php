<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\ConcurrencyResolving\DoNothingStrategy;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use Tcieslar\EventStore\Aggregate\Version;

class DoNothingStrategyTest extends TestCase
{
    public function testHandle(): void
    {
        $exception = new ConcurrencyException(
            CustomerId::create(),
            new AggregateType(Customer::class),
            Version::number(123),
            Version::number(122),
            new EventCollection(),
            new EventCollection()
        );
        $strategy = new DoNothingStrategy();
        $this->expectException(RuntimeException::class);
        $strategy->resolve($exception);
    }
}