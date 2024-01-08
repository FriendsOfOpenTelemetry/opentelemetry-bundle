<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use OpenTelemetry\API\Trace\SpanInterface;

final class TraceableStatement extends AbstractStatementMiddleware
{
    public function __construct(
        StatementInterface $statement,
        private DoctrineTracer $tracer,
    ) {
        parent::__construct($statement);
    }

    public function execute($params = null): Result
    {
        return $this->tracer->traceFunction('doctrine.dbal.statement.execute', function (SpanInterface $span) use ($params): Result {
            $span->setAttribute('db.params', $params);

            return parent::execute($params);
        });
    }
}
