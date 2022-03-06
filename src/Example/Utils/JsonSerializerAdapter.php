<?php

namespace Tcieslar\EventStore\Example\Utils;

use Tcieslar\EventSourcing\Event;
use Tcieslar\EventStore\Example\Event\DomainEventExample;
use Tcieslar\EventStore\Utils\EventSerializerInterface;

class JsonSerializerAdapter implements EventSerializerInterface
{
    public function __construct()
    {
    }

    /**
     * @throws \JsonException
     */
    public function seriazlize(Event $event, array $context = []): string
    {
        if (!$event instanceof DomainEventExample) {
            throw new \RuntimeException('This serializer uses only the ExampleDomainEvent class.');
        }
        $normalizeData = $event->normalize();
        if (array_key_exists(EventSerializerInterface::IGNORE_PROPERTY, $context)) {
            foreach ($context[EventSerializerInterface::IGNORE_PROPERTY] as $property) {
                unset($normalizeData[$property]);
            }
        }
        return json_encode($normalizeData, JSON_THROW_ON_ERROR);
    }

    public function deseriazlize(string $text, string $type, array $context = []): Event
    {
        $decodedData = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists(EventSerializerInterface::ADD_PROPERTY, $context)) {
            foreach ($context[EventSerializerInterface::ADD_PROPERTY] as $key => $data) {
                $decodedData[$key] = $data;
            }
        }

        return $type::denormalize($decodedData);
    }
}