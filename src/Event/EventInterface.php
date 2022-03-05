<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Event;

use DateTimeImmutable;
use Tcieslar\EventStore\Utils\Uuid;

interface EventInterface
{
    public function getEventId(): Uuid;

    public function getOccurredAt(): DateTimeImmutable;
}