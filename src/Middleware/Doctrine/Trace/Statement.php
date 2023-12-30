<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use OpenTelemetry\API\Trace\TracerInterface;

final class Statement extends AbstractStatementMiddleware
{
    public function __construct(
        private StatementInterface $statement,
        private TracerInterface $tracer,
        private string $sql,
    ) {
        parent::__construct($statement);
    }

    public function execute($params = null): Result
    {
        return parent::execute($params);
    }
}
