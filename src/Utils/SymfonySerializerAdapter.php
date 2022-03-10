<?php

namespace Tcieslar\EventStore\Utils;

use Symfony\Component\Serializer\Serializer;
use Tcieslar\EventSourcing\Event;

class SymfonySerializerAdapter implements EventSerializerInterface
{
    private Serializer $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function seriazlize(Event $event, array $context = []): string
    {
        return $this->serializer->serialize(
            $event,
            'json',
//            [AbstractNormalizer::IGNORED_ATTRIBUTES => $context[EventSerializerInterface::IGNORE_PROPERTY]]
        );
    }

    public function deseriazlize(string $text, string $type, array $context = []): Event
    {
        $response = $this->serializer->decode($text, 'json');

//        foreach ($context[EventSerializerInterface::ADD_PROPERTY] as $key => $data) {
//            $response[$key] = $data;
//        }

        return $this->serializer->denormalize(
            $response,
            $type
        );
    }
}