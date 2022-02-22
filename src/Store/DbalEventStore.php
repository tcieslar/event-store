<?php

namespace Tcieslar\EventStore\Store;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\TransactionIsolationLevel;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\EventStoreInterface;
use Tcieslar\EventStore\Exception\AggregateNotFoundException;
use Tcieslar\EventStore\Exception\ConcurrencyException;

class DbalEventStore implements EventStoreInterface
{
    private Connection $connection;
    private SerializerInterface $serializer;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(string $url, SerializerInterface $serializer)
    {
        $this->connect($url);
        $this->serializer = $serializer;
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws AggregateNotFoundException
     */
    public function loadFromStream(AggregateIdInterface $aggregateId, ?Version $afterVersion = null): EventStream
    {
        $stmt = $this->connection->prepare('SELECT id, type FROM aggregate WHERE aggregate_id = ?;');
        $stmt->bindValue(1, $aggregateId->toString());
        $result = $stmt->executeQuery();

        if (($aggregateRow = $result->fetchAssociative()) === false) {
            throw new AggregateNotFoundException($aggregateId);
        }

        if (!$afterVersion) {
            $stmt = $this->connection->prepare('SELECT * FROM event WHERE aggregate_id = ? ORDER BY version');
            $stmt->bindValue(1, $aggregateId->toString());
            $startVersion = Version::zero();
        } else {
            $stmt = $this->connection->prepare('SELECT * FROM event WHERE aggregate_id = ? AND version > ? ORDER BY version');
            $stmt->bindValue(1, $aggregateId->toString());
            $stmt->bindValue(2, $afterVersion->toString());
            $startVersion = clone $afterVersion;
        }

        $eventCollection = new EventCollection();
        $result = $stmt->executeQuery();
        $endVersion = null;
        while (($row = $result->fetchAssociative()) !== false) {
            $endVersion = Version::number($row['version']);
            $event = $this->serializer->deserialize($row['data'], $row['type'], 'json');
            $eventCollection->add($event);
        }
        $endVersion ??= $startVersion;

        return new EventStream(
            aggregateId: $aggregateId,
            aggregateType: new AggregateType($aggregateRow['type']),
            startVersion: $startVersion,
            endVersion: $endVersion,
            events: $eventCollection
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function appendToStream(AggregateIdInterface $aggregateId, AggregateType $aggregateType, Version $expectedVersion, EventCollection $events): Version
    {
        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare('SELECT version FROM aggregate WHERE aggregate_id = ?;');
            $stmt->bindValue(1, $aggregateId->toString());
            $result = $stmt->executeQuery();

            $versionNumber = $result->fetchOne();
            $actualVersion = $versionNumber!== false ? Version::number($versionNumber) : null;

            if (!$actualVersion) {
                $stmt = $this->connection->prepare('INSERT INTO aggregate(id, aggregate_id, type, version) VALUES(nextval(\'aggregate_id_seq\'), ?, ?, ?);');
                $stmt->bindValue(1, $aggregateId->toString());
                $stmt->bindValue(2, $aggregateType->toString());
                $stmt->bindValue(3, $expectedVersion->toString());
                $stmt->executeQuery();
                $actualVersion = $expectedVersion;
            }

            if (!$expectedVersion->isEqual($actualVersion)) {
                $newEventsStream = $this->loadFromStream($aggregateId, $expectedVersion);
                throw new ConcurrencyException($aggregateId, $aggregateType, $expectedVersion, $actualVersion, $events, $newEventsStream->events);
            }
            assert($actualVersion instanceof Version);

            $newVersion = clone $actualVersion;
            /** @var EventInterface $event */
            foreach ($events->getAll() as $event) {
                $newVersion = $newVersion->incremented();

                $stmt = $this->connection->prepare('INSERT INTO event(id, aggregate_id, data, type, version, occurred_at) VALUES(nextval(\'event_id_seq\'), ?, ?, ?, ?, ?);');
                $stmt->bindValue(1, $aggregateId->toString());
                $stmt->bindValue(2, $this->serializer->serialize(
                    $event,
                    'json',
                    [AbstractNormalizer::IGNORED_ATTRIBUTES => ['eventType', 'occurredAt', 'aggregateId']]));
                $stmt->bindValue(3, $event->getEventType()->toString());
                $stmt->bindValue(4, $newVersion->toString());
                $stmt->bindValue(5, $event->getOccurredAt()->format('Y-m-d H:i:s'));
                $stmt->executeQuery();
            }

            $stmt3 = $this->connection->prepare('UPDATE aggregate SET version = ? WHERE aggregate_id = ?;');
            $stmt3->bindValue(1, $newVersion->toString());
            $stmt3->bindValue(2, $aggregateId->toString());
            $stmt3->executeQuery();

            // $this->eventPublisher->publish($events);
            $this->connection->commit();

        } catch (\Throwable $throwable) {
            $this->connection->rollBack();
            throw $throwable;
        }

        return $newVersion;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function connect(string $url): void
    {
        $connectionParams = array(
            'url' => $url,
        );
        $this->connection = DriverManager::getConnection($connectionParams);
        $this->connection->setTransactionIsolation(TransactionIsolationLevel::REPEATABLE_READ);
        $this->checkStructure();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function checkStructure(): void
    {
        $stmt = $this->connection->prepare('SELECT EXISTS (
SELECT FROM information_schema.tables 
WHERE  table_name = \'event\' OR table_name = \'aggregate\'
   );
');
        $result = $stmt->executeQuery();
        if ($result->fetchAllAssociative()[0]['exists']) {
            return;
        }

        $this->connection->executeQuery('CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->connection->executeQuery('CREATE SEQUENCE aggregate_id_seq INCREMENT BY 1 MINVALUE 1 START 1;');
        $this->connection->executeQuery('CREATE TABLE event
(
    id           INT                            NOT NULL,
    aggregate_id UUID                           NOT NULL,
    data         JSON                           NOT NULL,
    type         VARCHAR(255)                   NOT NULL,
    version      BIGINT                         NOT NULL,
    occurred_at  TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY (id)
);');
        $this->connection->executeQuery('COMMENT ON COLUMN event.occurred_at IS \'(DC2Type:datetime_immutable)\';');
        $this->connection->executeQuery('CREATE TABLE aggregate
(
    id           INT          NOT NULL,
    aggregate_id UUID         NOT NULL,
    type         VARCHAR(255) NOT NULL,
    version      BIGINT       NOT NULL,
    PRIMARY KEY (id)
);');
        $this->connection->executeQuery('CREATE INDEX event_idx ON event (aggregate_id, version);');
        $this->connection->executeQuery('CREATE INDEX aggregate_idx ON aggregate (aggregate_id);');
    }
}