<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Example\Aggregate\Order;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class OrderTest extends TestCase
{
    public function testFields(): void
    {
        $orderId = new OrderId(Uuid::v4());
        $order = Order::create($orderId, 'order description');

        $this->assertEquals('order description', $order->getDescription());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->getCreatedAt());
    }

}