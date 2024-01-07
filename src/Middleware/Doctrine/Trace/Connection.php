<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\SemConv\TraceAttributes;

class Connection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $connection,
        private DoctrineTracer $tracer,
        private SpanInterface $driverSpan,
    ) {
        parent::__construct($connection);
    }

    /**
     * Disconnect.
     */
    public function __destruct()
    {
        $this->driverSpan->end();
    }

    public function prepare(string $sql): DriverStatement
    {
        return $this->tracer->traceFunction('doctrine.dbal.statement.prepare', function (SpanInterface $span) use ($sql): DriverStatement {
            $span->setAttribute(TraceAttributes::DB_STATEMENT, $sql);

            return new Statement(parent::prepare($sql), $this->tracer);
        });
    }

    public function query(string $sql): Result
    {
        return $this->tracer->traceFunction('doctrine.dbal.connection.query', function (SpanInterface $span) use ($sql): Result {
            $span->setAttribute(TraceAttributes::DB_STATEMENT, $sql);

            return parent::query($sql);
        });
    }

    public function exec(string $sql): int
    {
        return $this->tracer->traceFunction('doctrine.dbal.connection.exec', function (SpanInterface $span) use ($sql): int {
            $span->setAttribute(TraceAttributes::DB_STATEMENT, $sql);

            return parent::exec($sql);
        });
    }

    public function beginTransaction(): bool
    {
        return $this->tracer->traceFunction('doctrine.dbal.transaction.begin', function (SpanInterface $span): bool {
            return parent::beginTransaction();
        });
    }

    public function commit(): bool
    {
        return $this->tracer->traceFunction('doctrine.dbal.transaction.commit', function (SpanInterface $span): bool {
            return parent::commit();
        });
    }

    public function rollBack(): bool
    {
        return $this->tracer->traceFunction('doctrine.dbal.transaction.rollback', function (SpanInterface $span): bool {
            return parent::rollBack();
        });
    }
}
