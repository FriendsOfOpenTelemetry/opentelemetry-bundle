<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;

/**
 * @phpstan-import-type OverrideParams from DriverManager
 * @phpstan-import-type Params from DriverManager
 */
final class Driver extends AbstractDriverMiddleware
{
    public function __construct(
        private TracerInterface $tracer,
        DriverInterface $driver,
    ) {
        parent::__construct($driver);
    }

    /**
     * @param Params $params
     */
    public function connect(
        #[\SensitiveParameter]
        array $params
    ): Connection {
        $span = null;
        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('doctrine.dbal.connection')
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setAttribute(TraceAttributes::DB_SYSTEM, $this->getSemanticDbSystem())
                ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, $params['url'] ?? $params['path'])
                ->setAttribute(TraceAttributes::DB_NAME, $params['dbname'] ?? 'default')
                ->setAttribute(TraceAttributes::DB_USER, $params['user'])
            ;

            $span = $spanBuilder->setParent(Context::getCurrent())->startSpan();

            return new Connection(parent::connect($params), new DoctrineTracer($this->tracer), $span);
        } catch (Exception $exception) {
            if (null !== $span) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                $span->end();
            }
        }
    }

    private function getSemanticDbSystem(): string
    {
        // https://github.com/open-telemetry/semantic-conventions/blob/main/docs/attributes-registry/db.md
        return match (get_class($this->getDatabasePlatform())) {
            AbstractMySQLPlatform::class => 'mysql',
            DB2Platform::class => 'db2',
            OraclePlatform::class => 'oracle',
            PostgreSQLPlatform::class => 'postgresql',
            SqlitePlatform::class => 'sqlite',
            SQLServerPlatform::class => 'mssql',
            default => 'other_sql',
        };
    }
}
