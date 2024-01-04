<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;

final class Statement extends AbstractStatementMiddleware
{
    public function __construct(
        StatementInterface $statement,
    ) {
        parent::__construct($statement);
    }

    public function execute($params = null): Result
    {
        return parent::execute($params);
    }
}
