<?php declare(strict_types=1);

namespace Tcieslar\EventStore\Tests\Example\Aggregate;

use Tcieslar\EventStore\Aggregate\AbstractAggregate;
use DateTimeImmutable;
use Tcieslar\EventStore\Tests\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Tests\Example\Event\OrderCreatedEvent;
use Tcieslar\EventSourcing\Uuid;

class Order extends AbstractAggregate
{
    private OrderId $orderId;
    private string $description;
    private DateTimeImmutable $createdAt;

    public static function create(OrderId $orderId, string $description): self
    {
        $obj = new Order();
        $obj->apply(
            new OrderCreatedEvent(
                $orderId,
                $description
            )
        );
        return $obj;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function getId(): Uuid
    {
        return $this->orderId->getUuid();
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;

    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    protected function whenOrderCreatedEvent(OrderCreatedEvent $event): void
    {
        $this->orderId = $event->getOrderId();
        $this->description = $event->getDescription();
        $this->createdAt = $event->getOccurredAt();
    }
}