<?php

namespace Functional;

use Example\Aggregate\CustomerId;
use Example\Event\CustomerCreatedEvent;
use Example\Event\CustomerCredentialSetEvent;
use Example\Projection\CustomerProjection;
use Example\Projection\CustomerView;
use PHPUnit\Framework\TestCase;
use Projection\ProjectionManager;
use Storage\InMemoryProjectionStorage;
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

        /** @var ?CustomerView $view */
        $view = $projectionStorage->getView(CustomerView::class, $customerId);
        $this->assertEquals($customerId->toString(), $view?->customerId->toString());
    }
}