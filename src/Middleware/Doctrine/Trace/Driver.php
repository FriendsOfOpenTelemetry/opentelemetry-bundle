<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;

/**
 * Types extracted from \Doctrine\DBAL\DriverManager.
 *
 * @phpstan-type OverrideParams array{
 *     application_name?: string,
 *     charset?: string,
 *     dbname?: string,
 *     default_dbname?: string,
 *     driver?: key-of<DriverManager::DRIVER_MAP>,
 *     driverClass?: class-string<DriverInterface>,
 *     driverOptions?: array<int, mixed>,
 *     host?: string,
 *     password?: string,
 *     path?: string,
 *     persistent?: bool,
 *     platform?: AbstractPlatform,
 *     port?: int,
 *     serverVersion?: string,
 *     url?: string,
 *     user?: string,
 *     unix_socket?: string,
 * }
 * @phpstan-type Params array{
 *     application_name?: string,
 *     charset?: string,
 *     dbname?: string,
 *     defaultTableOptions?: array<string, mixed>,
 *     default_dbname?: string,
 *     driver?: key-of<DriverManager::DRIVER_MAP>,
 *     driverClass?: class-string<DriverInterface>,
 *     driverOptions?: array<int, mixed>,
 *     host?: string,
 *     keepSlave?: bool,
 *     keepReplica?: bool,
 *     master?: OverrideParams,
 *     memory?: bool,
 *     password?: string,
 *     path?: string,
 *     persistent?: bool,
 *     platform?: AbstractPlatform,
 *     port?: int,
 *     primary?: OverrideParams,
 *     replica?: array<OverrideParams>,
 *     serverVersion?: string,
 *     sharding?: array<string,mixed>,
 *     slaves?: array<OverrideParams>,
 *     url?: string,
 *     user?: string,
 *     wrapperClass?: class-string<ConnectionInterface>,
 *     unix_socket?: string,
 * }
 */
final class Driver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        private TracerInterface $tracer
    ) {
        parent::__construct($driver);
    }

    /**
     * @param Params $params
     */
    public function connect(
        #[\SensitiveParameter]
        array $params
    ) {
        $databasePlatform = $this->getDatabasePlatform();
        $spanBuilder = $this->tracer
            ->spanBuilder('doctrine.dbal.middleware.driver')
            ->setAttribute(TraceAttributes::DB_SYSTEM, $this->getSemanticDbSystem())
            ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, $params['url'])
            ->setAttribute(TraceAttributes::DB_NAME, $params['dbname'])
            ->setAttribute(TraceAttributes::DB_USER, $params['user'])
        ;

        $spanBuilder->setSpanKind(SpanKind::KIND_SERVER);

        $parent = Context::getCurrent();

        $span = $spanBuilder->setParent($parent)->startSpan();
        $scope = $span->storeInContext($parent)->activate();

        return new Connection(
            parent::connect($params),
            $this->tracer,
            $span,
            $scope,
        );
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
