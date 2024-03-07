<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use OpenTelemetry\API\Trace\SpanInterface;

final class TraceableStatementV4 extends AbstractStatementMiddleware
{
    public function __construct(
        StatementInterface $statement,
        private Tracer $tracer,
    ) {
        parent::__construct($statement);
    }

    public function execute(): Result
    {
        return $this->tracer->traceFunction('doctrine.dbal.statement.execute', function (SpanInterface $span): Result {
            return parent::execute();
        });
    }
}
