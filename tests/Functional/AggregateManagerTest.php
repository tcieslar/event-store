<?php

namespace Functional;

use DoNothingStrategy;
use Example\Customer;
use Example\CustomerCreatedEvent;
use Example\CustomerCredentialSetEvent;
use Example\CustomerId;
use Example\EventStore;
use InMemorySnapshotRepository;
use PhpSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use UnitOfWork;
use Version;

class AggregateManagerTest extends TestCase
{
    //todo:

    public function testFlush(): void
    {
        $customer = $this->createCustomer();

        // append To Stream
        $eventStore = $this->createMock(EventStore::class);
        $eventStore->expects($this->once())
            ->method('appendToStream')
            ->with($this->equalTo($customer->getId()),
                $this->equalTo(Version::createFirstVersion()),
                $this->callback(function (\EventCollection $changes) {
                    return count($changes) === 2 &&
                        $changes->get(0) instanceof CustomerCreatedEvent &&
                        $changes->get(1) instanceof CustomerCredentialSetEvent;
                })
            );

        $aggregateManager = new \AggregateManager(
            unitOfWork: new UnitOfWork(),
            eventStore: $eventStore,
            snapshotRepository: new InMemorySnapshotRepository(
                serializer: new PhpSerializer()
            ),
            concurrencyResolvingStrategy: new DoNothingStrategy()
        );

        $aggregateManager->addAggregate($customer);
        $aggregateManager->flush();
    }

    private function createCustomer(): Customer
    {
        $customerId = new CustomerId(Uuid::v4());
        return Customer::create($customerId, 'name');
    }
}