<?php

namespace Tcieslar\EventStore\Tests\Unit;

use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Utils\PhpSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class PhpSerializerTest extends TestCase
{
    public function testSerialize(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $customer->setName('new name');
        $serializer = new PhpSerializer();

        $result = $serializer->serialize($customer);
        $this->assertNotEmpty($result);
    }

    public function testUnserialize(): void
    {
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'test');
        $customer->setName('new name');
        $serializer = new PhpSerializer();

        $result = $serializer->serialize($customer);
        $aggregate = $serializer->unserialize($result);

        $this->assertInstanceOf(Customer::class, $aggregate);
        $this->assertEquals($aggregate->getId(), $customerId);
        $this->assertEquals('new name', $customer->getName());
    }
}