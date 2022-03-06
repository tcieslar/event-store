<?php

namespace Tcieslar\EventStore\Tests\Functional;

use Tcieslar\EventSourcing\EventCollection;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Example\Event\CustomerCreatedEvent;
use Tcieslar\EventStore\Example\Event\CustomerCredentialSetEvent;
use Tcieslar\EventStore\Example\Event\OrderAddedEvent;
use Tcieslar\EventStore\Projection\ProjectionManager;
use Tcieslar\EventStore\Projection\InMemoryProjectionStorage;

class ProjectionManagerTest
{
//    public function testProjectViews(): void
//    {
//        $projectionStorage = new InMemoryProjectionStorage();
//        $projectionManager = new ProjectionManager(
//            $projectionStorage,
//            [
//                new CustomerProjection()
//            ]
//        );
//
//        $customerId = CustomerId::create();
//        $projectionManager->projectViews(
//            new CustomerCreatedEvent(
//                $customerId
//            )
//        );
//        $projectionManager->projectViews(
//            new CustomerCredentialSetEvent(
//                $customerId,
//                'test 2'
//            )
//        );
//
//        $projectionManager->projectViews(
//            new OrderAddedEvent(
//                $customerId,
//                OrderId::create(),
//                'to jest zamównienie'
//            )
//        );
//
//        $projectionManager->projectViews(
//            new OrderAddedEvent(
//                $customerId,
//                OrderId::create(),
//                'to jest zamównienie 2'
//            )
//        );
//
//        /** @var ?CustomerView $view */
//        $view = $projectionStorage->getView(CustomerView::class, $customerId);
//
//        $this->assertInstanceOf(CustomerView::class, $view);
//        $this->assertInstanceOf(\DateTimeImmutable::class, $view->createdAt);
//        $this->assertEquals($customerId->toString(), $view?->customerId->toString());
//        $this->assertEquals('test 2', $view->name);
//        $this->assertEquals([
//            'to jest zamównienie',
//            'to jest zamównienie 2'
//        ],
//            $view->orders);
//    }
//
//    public function testProjectEventCollection(): void
//    {
//        $projectionStorage = new InMemoryProjectionStorage();
//        $projectionManager = new ProjectionManager(
//            $projectionStorage,
//            [
//                new CustomerProjection(),
//                new OrderProjection()
//            ]
//        );
//
//        $customerId = CustomerId::create();
//        $eventCollection = new EventCollection();
//        $eventCollection->add(
//            new CustomerCreatedEvent(
//                $customerId
//            )
//        );
//        $eventCollection->add(
//            new CustomerCredentialSetEvent(
//                $customerId,
//                'test 2'
//            )
//        );
//        $eventCollection->add(
//            new OrderAddedEvent(
//                $customerId,
//                OrderId::create(),
//                'to jest zamównienie'
//            )
//        );
//        $eventCollection->add(
//            new OrderAddedEvent(
//                $customerId,
//                OrderId::create(),
//                'to jest zamównienie 2'
//            )
//        );
//
//        $projectionManager->projectViewsByEventCollection($eventCollection);
//
//        /** @var ?CustomerView $view */
//        $view = $projectionStorage->getView(CustomerView::class, $customerId);
//        $this->assertInstanceOf(CustomerView::class, $view);
//        $this->assertInstanceOf(\DateTimeImmutable::class, $view->createdAt);
//        $this->assertEquals($customerId->toString(), $view?->customerId->toString());
//        $this->assertEquals('test 2', $view->name);
//        $this->assertEquals([
//            'to jest zamównienie',
//            'to jest zamównienie 2'
//        ],
//            $view->orders);
//    }
}