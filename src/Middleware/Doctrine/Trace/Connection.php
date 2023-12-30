<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;

class Connection extends AbstractConnectionMiddleware
{
    public function __construct(
        private ConnectionInterface $connection,
        private TracerInterface $tracer,
        private ?SpanInterface $span = null,
        private ?ScopeInterface $scope = null,
    ) {
        parent::__construct($connection);
    }

    public function __destruct()
    {
        if (null === $this->scope) {
            return;
        }
        $this->scope->detach();

        if (null === $this->span) {
            return;
        }
        $this->span->end();
    }

    public function prepare(string $sql): DriverStatement
    {
        return new Statement(
            parent::prepare($sql),
            $this->tracer,
            $sql,
        );
    }

    public function query(string $sql): Result
    {
        return parent::query($sql);
    }

    public function exec(string $sql): int
    {
        return parent::exec($sql);
    }

    public function beginTransaction(): bool
    {
        return parent::beginTransaction();
    }

    public function commit(): bool
    {
        return parent::commit();
    }

    public function rollBack(): bool
    {
        return parent::rollBack();
    }
}
