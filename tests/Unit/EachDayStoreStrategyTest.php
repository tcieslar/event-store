<?php

namespace Tcieslar\EventStore\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Snapshot\Snapshot;
use Tcieslar\EventStore\Snapshot\StoreStrategy\EachDayStoreStrategy;

class EachDayStoreStrategyTest extends TestCase
{
    public function testToday(): void
    {
        $customer = Customer::create(CustomerId::create(), 'test');
        $snapshot = new Snapshot(
            $customer,
            Version::number(2),
            new \DateTimeImmutable()

        );

        $strategy = new EachDayStoreStrategy();
        $this->assertFalse($strategy->whetherToStoreNew($snapshot));
    }

    public function testPast(): void
    {
        $customer = Customer::create(CustomerId::create(), 'test');
        $snapshot = new Snapshot(
            $customer,
            Version::number(2),
            (new \DateTimeImmutable())->modify('-1 day')
        );

        $strategy = new EachDayStoreStrategy();
        $this->assertTrue($strategy->whetherToStoreNew($snapshot));
    }

    public function testFuture(): void
    {
        $customer = Customer::create(CustomerId::create(), 'test');
        $snapshot = new Snapshot(
            $customer,
            Version::number(2),
            (new \DateTimeImmutable())->modify('+1 day')
        );

        $strategy = new EachDayStoreStrategy();
        $this->assertFalse($strategy->whetherToStoreNew($snapshot));
    }

}