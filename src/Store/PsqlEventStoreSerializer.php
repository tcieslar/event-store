<?php

namespace Tcieslar\EventStore\Store;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Tcieslar\EventSourcing\Event;

class PsqlEventStoreSerializer
{
    private Serializer $serializer;

    public function __construct(?Serializer $serializer = null)
    {
        if ($serializer) {
            $this->serializer = $serializer;
            return;
        }
        $this->symfonySerializerFactory();
    }

    private function symfonySerializerFactory(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new DateTimeNormalizer(),
            new PropertyNormalizer(
                null, null, new ReflectionExtractor()
            )];
        $this->serializer = new Serializer(
            $normalizers, $encoders
        );
    }

    public function seriazlize(Event $event): string
    {
        return $this->serializer->serialize(
            $event,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => [
                'occurredAt', 'eventId'
            ]]
        );
    }

    public function deseriazlize(string $text, string $type, array $properties = []): Event
    {
        $response = $this->serializer->decode($text, 'json');
        foreach ($properties as $key => $data) {
            $response[$key] = $data;
        }

        return $this->serializer->denormalize(
            $response,
            $type
        );
    }
}