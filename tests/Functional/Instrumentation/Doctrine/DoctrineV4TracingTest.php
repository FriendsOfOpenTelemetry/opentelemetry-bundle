<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
final class DoctrineV4TracingTest extends KernelTestCase
{
    use TracingTestCaseTrait;
    use DoctrineTestCaseTrait;

    private ?Connection $connection;

    public static function setUpBeforeClass(): void
    {
        if (!self::isDoctrineDBALVersion4Installed()) {
            self::markTestSkipped('This test requires the version of the "doctrine/dbal" Composer package to be >= 4.0.');
        }
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->connection = self::getContainer()->get('doctrine')->getConnection();
    }

    public function testConnection(): void
    {
        self::assertSame('main', $this->connection->getDatabase());
        self::assertTrue($this->connection->isConnected());

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'doctrine.dbal.connection');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributesSubSet($mainSpan, [
            'db.namespace' => 'default',
            'doctrine.user' => 'root',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);

        $querySpan = self::getSpans()[1];
        self::assertSpanName($querySpan, 'doctrine.dbal.connection.query');
        self::assertSpanStatus($querySpan, StatusData::unset());
        self::assertSpanAttributes($querySpan, [
            'db.query.text' => 'SELECT \'main\'',
        ]);
        self::assertSpanEventsCount($querySpan, 0);
    }

    public function testException(): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.error_connection');

        try {
            $connection->getDatabase();
        } catch (ConnectionException $exception) {
        }

        self::assertFalse($connection->isConnected());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'doctrine.dbal.connection');
        self::assertSpanStatus($mainSpan, new StatusData(StatusCode::STATUS_ERROR, 'SQLSTATE[HY000] [14] unable to open database file'));
        //        self::assertSpanAttributesSubSet($mainSpan, [
        //            'db.name' => 'default',
        //        ]);
        self::assertSpanEventsCount($mainSpan, 1);

        $exceptionEvent = $mainSpan->getEvents()[0];
        self::assertSpanEventName($exceptionEvent, 'exception');
        self::assertSpanEventAttributesSubSet($exceptionEvent, [
            'exception.type' => 'Doctrine\DBAL\Driver\PDO\Exception',
            'exception.message' => 'SQLSTATE[HY000] [14] unable to open database file',
        ]);
    }

    public function testQuery(): void
    {
        $result = $this->connection->executeQuery(<<<'SQL'
        SELECT * FROM dummy
        SQL);
        self::assertEquals([], $result->fetchAllAssociative());

        self::assertSpansCount(2);

        $querySpan = self::getSpans()[1];
        self::assertSpanName($querySpan, 'doctrine.dbal.connection.query');
        self::assertSpanStatus($querySpan, StatusData::unset());
        self::assertSpanAttributes($querySpan, [
            'db.query.text' => 'SELECT * FROM dummy',
        ]);
        self::assertSpanEventsCount($querySpan, 0);
    }

    public function testQueryException(): void
    {
        try {
            $result = $this->connection->executeQuery(<<<'SQL'
            SELECT * FROM error
            SQL
            );
            self::assertEquals([], $result->fetchAllAssociative());
        } catch (TableNotFoundException $exception) {
        }

        self::assertSpansCount(2);

        $querySpan = self::getSpans()[1];
        self::assertSpanName($querySpan, 'doctrine.dbal.connection.query');
        self::assertSpanStatus($querySpan, new StatusData(StatusCode::STATUS_ERROR, 'SQLSTATE[HY000]: General error: 1 no such table: error'));
        self::assertSpanAttributes($querySpan, [
            'db.query.text' => 'SELECT * FROM error',
        ]);

        self::assertSpanEventsCount($querySpan, 1);

        $exceptionEvent = $querySpan->getEvents()[0];
        self::assertSpanEventName($exceptionEvent, 'exception');
        self::assertSpanEventAttributesSubSet($exceptionEvent, [
            'exception.type' => 'Doctrine\DBAL\Driver\PDO\Exception',
        ]);
    }

    public function testStatement(): void
    {
        $result = $this->connection->executeStatement(<<<'SQL'
        SELECT * FROM dummy
        SQL);
        self::assertSame(0, $result);

        self::assertSpansCount(2);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'doctrine.dbal.connection.exec');
        self::assertSpanStatus($mainSpan, StatusData::unset());
        self::assertSpanAttributes($mainSpan, [
            'db.query.text' => 'SELECT * FROM dummy',
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testPrepare(): void
    {
        $statement = $this->connection->prepare(<<<'SQL'
        SELECT * FROM dummy
        SQL);
        self::assertSame(0, $statement->executeStatement());
        self::assertEquals([], $statement->executeQuery()->fetchAllAssociative());

        self::assertSpansCount(4);

        $prepareSpan = self::getSpans()[1];
        self::assertSpanName($prepareSpan, 'doctrine.dbal.statement.prepare');
        self::assertSpanStatus($prepareSpan, StatusData::unset());
        self::assertSpanAttributes($prepareSpan, [
            'db.query.text' => 'SELECT * FROM dummy',
        ]);
        self::assertSpanEventsCount($prepareSpan, 0);

        $executeSpan = self::getSpans()[2];
        self::assertSpanName($executeSpan, 'doctrine.dbal.statement.execute');
        self::assertSpanStatus($executeSpan, StatusData::unset());
        self::assertSpanAttributes($executeSpan, []);
        self::assertSpanEventsCount($executeSpan, 0);

        $querySpan = self::getSpans()[3];
        self::assertSpanName($querySpan, 'doctrine.dbal.statement.execute');
        self::assertSpanStatus($querySpan, StatusData::unset());
        self::assertSpanAttributes($querySpan, []);
        self::assertSpanEventsCount($querySpan, 0);
    }

    public function testTransactionCommit(): void
    {
        $this->connection->beginTransaction();

        $result = $this->connection->executeStatement(<<<'SQL'
        SELECT * FROM dummy
        SQL);
        self::assertSame(0, $result);

        $this->connection->commit();

        self::assertSpansCount(4);

        $beginSpan = self::getSpans()[1];
        self::assertSpanName($beginSpan, 'doctrine.dbal.transaction.begin');
        self::assertSpanStatus($beginSpan, StatusData::unset());
        self::assertSpanAttributes($beginSpan, []);
        self::assertSpanEventsCount($beginSpan, 0);

        $execSpan = self::getSpans()[2];
        self::assertSpanName($execSpan, 'doctrine.dbal.connection.exec');
        self::assertSpanStatus($execSpan, StatusData::unset());
        self::assertSpanAttributes($execSpan, [
            'db.query.text' => 'SELECT * FROM dummy',
        ]);
        self::assertSpanEventsCount($execSpan, 0);

        $commitSpan = self::getSpans()[3];
        self::assertSpanName($commitSpan, 'doctrine.dbal.transaction.commit');
        self::assertSpanStatus($commitSpan, StatusData::unset());
        self::assertSpanAttributes($commitSpan, []);
        self::assertSpanEventsCount($commitSpan, 0);
    }

    public function testTransactionRollback(): void
    {
        $this->connection->beginTransaction();

        $result = $this->connection->executeStatement(<<<'SQL'
        SELECT * FROM dummy
        SQL);
        self::assertSame(0, $result);

        $this->connection->rollBack();

        self::assertSpansCount(4);

        $beginSpan = self::getSpans()[1];
        self::assertSpanName($beginSpan, 'doctrine.dbal.transaction.begin');
        self::assertSpanStatus($beginSpan, StatusData::unset());
        self::assertSpanAttributes($beginSpan, []);
        self::assertSpanEventsCount($beginSpan, 0);

        $execSpan = self::getSpans()[2];
        self::assertSpanName($execSpan, 'doctrine.dbal.connection.exec');
        self::assertSpanStatus($execSpan, StatusData::unset());
        self::assertSpanAttributes($execSpan, [
            'db.query.text' => 'SELECT * FROM dummy',
        ]);
        self::assertSpanEventsCount($execSpan, 0);

        $rollbackSpan = self::getSpans()[3];
        self::assertSpanName($rollbackSpan, 'doctrine.dbal.transaction.rollback');
        self::assertSpanStatus($rollbackSpan, StatusData::unset());
        self::assertSpanAttributes($rollbackSpan, []);
        self::assertSpanEventsCount($rollbackSpan, 0);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->connection->close();
    }
}
