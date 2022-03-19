<?php

namespace Tcieslar\EventStore\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

use Tcieslar\EventStore\Aggregate\AggregateManager;
use Tcieslar\EventStore\Aggregate\UnitOfWork;
use Tcieslar\EventStore\ConcurrencyResolving\SoftResolvingStrategy;
use Tcieslar\EventStore\EventPublisher\FileEventPublisher;
use Tcieslar\EventStore\Tests\Example\Aggregate\Customer;
use Tcieslar\EventStore\Tests\Example\Aggregate\CustomerId;
use Tcieslar\EventStore\Tests\Example\Aggregate\Order;
use Tcieslar\EventStore\Tests\Example\Aggregate\OrderId;
use Tcieslar\EventStore\Tests\Example\Repository\CustomerRepository;
use Tcieslar\EventStore\Snapshot\RedisSnapshotRepository;
use Tcieslar\EventStore\Snapshot\StoreStrategy\EachTimeStoreStrategy;
use Tcieslar\EventStore\Store\PsqlEventStore;
use Tcieslar\EventStore\Example\Utils\JsonSerializerAdapter;
use Tcieslar\EventStore\Utils\EventSerializerInterface;
use Tcieslar\EventStore\Store\PsqlEventStoreSerializer;

/**
 * @group integration
 */
class AggregateManagerWithDbalAndRedisTest extends TestCase
{
    private static string $postgreUrl = 'postgres:test@localhost:5432/event_store?serverVersion=14&charset=utf8';
    private static string $redisHost = '127.0.0.1';

    public function testIntegration(): void
    {
        $serializer = new PsqlEventStoreSerializer();
        $unitOfWork = new UnitOfWork();
        $eventPublisher = new FileEventPublisher();
        $eventStore = new PsqlEventStore(self::$postgreUrl, $serializer, $eventPublisher);
        $snapshotRepository = new RedisSnapshotRepository(self::$redisHost);
        $concurrencyResolvingStrategy = new SoftResolvingStrategy(
            eventStore: $eventStore
        );
        $aggregateManager = new AggregateManager(
            unitOfWork: $unitOfWork,
            eventStore: $eventStore,
            snapshotRepository: $snapshotRepository,
            concurrencyResolvingStrategy: $concurrencyResolvingStrategy,
            snapshotStoreStrategy: new EachTimeStoreStrategy()
        );
        $customerRepository = new CustomerRepository($aggregateManager);

        // save
        $customerId = CustomerId::create();
        $customer = Customer::create($customerId, 'name 1');
        $customerRepository->addAggregate($customer);
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
        $customer3->addOrder(Order::create(OrderId::create(), 'test'));
        $aggregateManager->flush();
        $aggregateManager->reset();

        $customer4 = $customerRepository->find($customerId);
        $this->assertEquals('name 2', $customer4->getName());
    }

}