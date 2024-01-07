<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Middleware\Doctrine\Trace;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\SemConv\TraceAttributes;

/**
 * @phpstan-import-type OverrideParams from DriverManager
 * @phpstan-import-type Params from DriverManager
 */
final class Driver extends AbstractDriverMiddleware
{
    private Tracer $tracer;

    public function __construct(
        TracerInterface $tracer,
        DriverInterface $driver,
    ) {
        parent::__construct($driver);

        $this->tracer = new Tracer($tracer);
    }

    /**
     * @param Params $params
     */
    public function connect(
        #[\SensitiveParameter]
        array $params
    ) {
        return $this->tracer->traceFunction('doctrine.dbal.driver.connect', function (SpanInterface $span) use ($params) {
            $span
                ->setAttribute(TraceAttributes::DB_SYSTEM, $this->getSemanticDbSystem())
                ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, $params['url'] ?? $params['path'])
                ->setAttribute(TraceAttributes::DB_NAME, $params['dbname'] ?? 'default')
                ->setAttribute(TraceAttributes::DB_USER, $params['user'])
            ;

            return new Connection(parent::connect($params), $this->tracer, $span);
        });
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
