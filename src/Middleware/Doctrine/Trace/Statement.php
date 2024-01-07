<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use OpenTelemetry\API\Trace\SpanInterface;

final class Statement extends AbstractStatementMiddleware
{
    public function __construct(
        StatementInterface $statement,
        private Tracer $tracer,
    ) {
        parent::__construct($statement);
    }

    public function execute($params = null): Result
    {
        return $this->tracer->traceFunction('doctrine.dbal.statement.execute', function (SpanInterface $span) use ($params) {
            $span->setAttribute('db.params', $params);

            return parent::execute($params);
        });
    }
}
