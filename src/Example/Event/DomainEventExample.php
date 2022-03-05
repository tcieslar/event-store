<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Example\Event;

use DateTimeImmutable;
use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Utils\Uuid;

abstract class DomainEventExample implements EventInterface
{
    protected Uuid $uuid;
    protected DateTimeImmutable $occurredAt;

    public function __construct(
        ?Uuid              $uuid,
        ?DateTimeImmutable $occurredAt
    )
    {
        $this->uuid = $uuid ?? Uuid::random();
        $this->occurredAt = $occurredAt ?? DateTimeImmutable::createFromFormat(
                DATE_RFC3339,
                (new \DateTimeImmutable())->format(DATE_RFC3339)
            );
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    abstract public function normalize(): array;

    abstract public static function denormalize(array $data): static;
}