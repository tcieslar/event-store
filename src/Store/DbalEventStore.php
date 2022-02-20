<?php

namespace Tcieslar\EventStore\Store;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\TransactionIsolationLevel;
use Tcieslar\EventStore\Aggregate\AggregateIdInterface;
use Tcieslar\EventStore\Aggregate\AggregateType;
use Tcieslar\EventStore\Aggregate\Version;
use Tcieslar\EventStore\Event\EventCollection;
use Tcieslar\EventStore\Event\EventInterface;
use Tcieslar\EventStore\Event\EventStream;
use Tcieslar\EventStore\EventStoreInterface;
use Tcieslar\EventStore\Exception\AggregateNotFoundException;
use Tcieslar\EventStore\Exception\ConcurrencyException;
use Tcieslar\EventStore\Utils\SerializerInterface;

class DbalEventStore implements EventStoreInterface
{

    private Connection $connection;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(

    )
    {
        $this->connect();
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
        $stmt = $this->connection->prepare('SELECT id FROM aggregate WHERE aggregate_id = ?;');
        $stmt->bindValue(1, $aggregateId->toString());
        $result = $stmt->executeQuery();

        if ($result->rowCount() !== 1) {
            throw new AggregateNotFoundException($aggregateId);
        }

        $stmt = $this->connection->prepare('SELECT * FROM event WHERE aggregate_id = ? ORDER BY version');
        $stmt->bindValue(1, $aggregateId->toString());
        $result = $stmt->executeQuery();
        $resultAss = $result->fetchAssociative();
        if (false !== $resultAss) {
            foreach ($resultAss as $item) {
                //var_dump($item);
            }
        }

        /*if (!$afterVersion) {
            return $this->storage->getEventStream($aggregateId);
        }*/

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

            $actualVersion = $result->fetchOne() ? Version::number($result->fetchOne()) : null;
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

            $newVersion = $actualVersion;
            /** @var EventInterface $event */
            foreach ($events->getAll() as $event) {
                $newVersion = $newVersion->incremented();

                $stmt = $this->connection->prepare('INSERT INTO event(id, aggregate_id, data, type, version, occurred_at) VALUES(nextval(\'event_id_seq\'), ?, ?, ?, ?, ?);');
                $stmt->bindValue(1, $aggregateId->toString());
                // todo:
                $stmt->bindValue(2, json_encode($event, JSON_THROW_ON_ERROR));
                $stmt->bindValue(3, $event->getEventType()->toString());
                $stmt->bindValue(4, $newVersion->toString());
                $stmt->bindValue(5, $event->getOccurredAt()->format('Y-m-d H:i:s'));
                $stmt->executeQuery();

                $stmt = $this->connection->prepare('UPDATE aggregate SET version = ? WHERE aggregate_id = ?;');
                $stmt->bindValue(1, $newVersion->toString());
                $stmt->bindValue(2, $aggregateId->toString());

            }
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
    private function connect(): void
    {
        $connectionParams = array(
            'url' => 'postgresql://postgres:test@localhost:5432/event_store?serverVersion=14&charset=utf8',
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