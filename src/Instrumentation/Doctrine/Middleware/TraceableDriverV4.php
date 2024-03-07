<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;

/**
 * @phpstan-import-type OverrideParams from DriverManager
 * @phpstan-import-type Params from DriverManager
 */
final class TraceableDriverV4 extends AbstractDriverMiddleware
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
        $scope = Context::storage()->scope();
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('doctrine.dbal.connection')
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setParent($scope?->context())
//                ->setAttribute(TraceAttributes::DB_SYSTEM, $this->getSemanticDbSystem($connection->getServerVersion()))
                ->setAttribute(TraceAttributes::DB_NAME, $params['dbname'] ?? 'default')
                ->setAttribute(TraceAttributes::DB_USER, $params['user'])
            ;

            $span = $spanBuilder->startSpan();
            if (null === $scope) {
                $scope = $span->storeInContext(Context::getCurrent())->activate();
            }

            $connection = parent::connect($params);
            $span->setAttribute(TraceAttributes::DB_SYSTEM, $this->getSemanticDbSystem($connection->getServerVersion()));
            $span->setStatus(StatusCode::STATUS_OK);

            return new TraceableConnection($connection, new Tracer($this->tracer));
        } catch (Exception $exception) {
            $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            throw $exception;
        } finally {
            $scope?->detach();
            if ($span instanceof SpanInterface) {
                $span->end();
            }
        }
    }

    private function getSemanticDbSystem(string $serverVersion): string
    {
        // https://github.com/open-telemetry/semantic-conventions/blob/main/docs/attributes-registry/db.md
        return match (get_class($this->getDatabasePlatform(new StaticServerVersionProvider($serverVersion)))) {
            AbstractMySQLPlatform::class => 'mysql',
            DB2Platform::class => 'db2',
            OraclePlatform::class => 'oracle',
            PostgreSQLPlatform::class => 'postgresql',
            SQLitePlatform::class => 'sqlite',
            SQLServerPlatform::class => 'mssql',
            default => 'other_sql',
        };
    }
}
