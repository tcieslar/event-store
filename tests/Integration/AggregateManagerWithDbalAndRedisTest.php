<?php

namespace Tcieslar\EventStore\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;
use Tcieslar\EventStore\Aggregate\AggregateManager;
use Tcieslar\EventStore\Aggregate\UnitOfWork;
use Tcieslar\EventStore\ConcurrencyResolving\SoftResolvingStrategy;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Example\Aggregate\Customer;
use Tcieslar\EventStore\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Example\Aggregate\Order;
use Tcieslar\EventStore\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Example\Repository\CustomerRepository;
use Tcieslar\EventStore\Snapshot\RedisSnapshotRepository;
use Tcieslar\EventStore\Store\DbalEventStore;

/**
 * @group integration
 */
class AggregateManagerWithDbalAndRedisTest extends TestCase
{
    private static string $postgreUrl = 'postgresql://postgres:test@localhost:5432/event_store?serverVersion=14&charset=utf8';
    private static string $redisHost = '127.0.0.1';

    public function testIntegration(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $unitOfWork = new UnitOfWork();
        $eventPublisher = new FileEventPublisher();
        $eventStore = new DbalEventStore(self::$postgreUrl, $serializer, $eventPublisher);
        $snapshotRepository = new RedisSnapshotRepository(self::$redisHost);
        $concurrencyResolvingStrategy = new SoftResolvingStrategy(
            eventStore: $eventStore
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: $concurrencyResolvingStrategy
        );
        $customerRepository = new CustomerRepository($aggregateManager);

        // save
        $customerId = new CustomerId(Uuid::v4());
        $customer = Customer::create($customerId, 'name 1');
        $customerRepository->add($customer);
        $aggregateManager->flush();
        $aggregateManager->reset();

        // load form postgres
        $customer2 = $customerRepository->find($customerId);
        $this->assertInstanceOf(Customer::class, $customer2);
        //$this->assertEquals(Customer::class, $customer2);
        $aggregateManager->reset();

        // load from redis
        $customer3 = $customerRepository->find($customerId);
        $this->assertInstanceOf(Customer::class, $customer3);

        // add event
        $customer3->setName('name 2');
        $customer3->addOrder(Order::create(new OrderId(Uuid::v4()), 'test'));
        $aggregateManager->flush();
        $aggregateManager->reset();

        $customer4 = $customerRepository->find($customerId);
        $this->assertEquals('name 2', $customer4->getName());
    }
}