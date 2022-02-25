<?php

namespace Tcieslar\EventStore\Utils;

use Tcieslar\EventStore\Event\EventInterface;

interface EventSerializerInterface
{
    public const IGNORE_PROPERTY = 'ignore_property';
    public const ADD_PROPERTY = 'add_property';

    public function seriazlize(EventInterface $event, array $context = []): string;
    public function deseriazlize(string $text, string $type, array $context = []): EventInterface;
}