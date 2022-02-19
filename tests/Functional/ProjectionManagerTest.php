<?php

namespace Tcieslar\EventStore\Tests\Functional;

use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Example\Event\OrderAddedEvent;
use Tcieslar\EventStore\Example\Projection\CustomerProjection;
use Tcieslar\EventStore\Example\Projection\CustomerView;
use Tcieslar\EventStore\Example\Projection\OrderProjection;
use PHPUnit\Framework\TestCase;
use Tcieslar\EventStore\Projection\ProjectionManager;
use Tcieslar\EventStore\Projection\InMemoryProjectionStorage;
use Symfony\Component\Uid\Uuid;

class ProjectionManagerTest extends TestCase
{
    public function testProjectViews(): void
    {
        $projectionStorage = new InMemoryProjectionStorage();
        $projectionManager = new ProjectionManager(
            $projectionStorage,
            [
                new CustomerProjection()
            ]
        );

        $customerId = new CustomerId(
            Uuid::v4()
        );
        $projectionManager->projectViews(
            new CustomerCreatedEvent(
                $customerId
            )
        );
        $projectionManager->projectViews(
            new CustomerCredentialSetEvent(
                $customerId,
                'test 2'
            )
        );

        $projectionManager->projectViews(
            new OrderAddedEvent(
                $customerId,
                new OrderId(Uuid::v4()),
                'to jest zamównienie'
            )
        );

        $projectionManager->projectViews(
            new OrderAddedEvent(
                $customerId,
                new OrderId(Uuid::v4()),
                'to jest zamównienie 2'
            )
        );

        /** @var ?CustomerView $view */
        $view = $projectionStorage->getView(CustomerView::class, $customerId);

        $this->assertInstanceOf(CustomerView::class, $view);
        $this->assertInstanceOf(\DateTimeImmutable::class, $view->createdAt);
        $this->assertEquals($customerId->toString(), $view?->customerId->toString());
        $this->assertEquals('test 2', $view->name);
        $this->assertEquals([
            'to jest zamównienie',
            'to jest zamównienie 2'
        ],
            $view->orders);
    }

    public function testProjectEventCollection(): void
    {
        $projectionStorage = new InMemoryProjectionStorage();
        $projectionManager = new ProjectionManager(
            $projectionStorage,
            [
                new CustomerProjection(),
                new OrderProjection()
            ]
        );

        $customerId = new CustomerId(
            Uuid::v4()
        );
        $eventCollection = new EventCollection();
        $eventCollection->add(
            new CustomerCreatedEvent(
                $customerId
            )
        );
        $eventCollection->add(
            new CustomerCredentialSetEvent(
                $customerId,
                'test 2'
            )
        );
        $eventCollection->add(
            new OrderAddedEvent(
                $customerId,
                new OrderId(Uuid::v4()),
                'to jest zamównienie'
            )
        );
        $eventCollection->add(
            new OrderAddedEvent(
                $customerId,
                new OrderId(Uuid::v4()),
                'to jest zamównienie 2'
            )
        );

        $projectionManager->projectViewsByEventCollection($eventCollection);

        /** @var ?CustomerView $view */
        $view = $projectionStorage->getView(CustomerView::class, $customerId);
        $this->assertInstanceOf(CustomerView::class, $view);
        $this->assertInstanceOf(\DateTimeImmutable::class, $view->createdAt);
        $this->assertEquals($customerId->toString(), $view?->customerId->toString());
        $this->assertEquals('test 2', $view->name);
        $this->assertEquals([
            'to jest zamównienie',
            'to jest zamównienie 2'
        ],
            $view->orders);
    }
}