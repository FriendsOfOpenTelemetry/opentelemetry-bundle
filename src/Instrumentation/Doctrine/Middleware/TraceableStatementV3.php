<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use Psr\Log\LoggerInterface;

final class TraceableStatementV3 extends AbstractStatementMiddleware
{
    public function __construct(
        StatementInterface $statement,
        private Tracer $tracer,
        private ?LoggerInterface $logger = null,
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
