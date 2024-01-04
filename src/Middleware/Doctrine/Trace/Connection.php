<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;

class Connection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $connection,
    ) {
        parent::__construct($connection);
    }

    public function prepare(string $sql): DriverStatement
    {
        return new Statement(parent::prepare($sql));
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
