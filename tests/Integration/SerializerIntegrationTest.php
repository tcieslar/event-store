<?php

namespace Tcieslar\EventStore\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Example\Event\OrderAddedEvent;

class SerializerIntegrationTest
{
    private function testIntegration(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $event = new OrderAddedEvent(
            new CustomerId(Uuid::v4()),
            new OrderId(Uuid::v4()),
            'test'
        );
        $test = $serializer->serialize($event, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['occurredAt', 'eventType', 'aggregateId']]);
        sleep(1);
        $obj = $serializer->deserialize($test, OrderAddedEvent::class, 'json');

        $this->assertInstanceOf(OrderAddedEvent::class, $obj);
        $this->assertEquals($event->getOccurredAt(), $obj->getOccurredAt());
        $this->assertEquals(get_class($event), get_class($obj));

        var_dump($test);
    }

}