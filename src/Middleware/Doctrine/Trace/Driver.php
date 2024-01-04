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
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SemConv\TraceAttributes;

/**
 * @phpstan-import-type OverrideParams from DriverManager
 * @phpstan-import-type Params from DriverManager
 */
final class Driver extends AbstractDriverMiddleware
{
    private ?ScopeInterface $scope = null;
    private ?SpanInterface $span = null;

    public function __construct(
        private readonly TracerInterface $tracer,
        DriverInterface $driver,
    ) {
        parent::__construct($driver);
    }

    public function __destruct()
    {
        if (null === $this->scope) {
            return;
        }

        $this->scope->detach();

        if (null === $this->span) {
            return;
        }

        $this->span->end();
    }

    /**
     * @param Params $params
     */
    public function connect(
        #[\SensitiveParameter]
        array $params
    ) {
        $spanBuilder = $this->tracer
            ->spanBuilder('doctrine.dbal.driver')
            ->setAttribute(TraceAttributes::DB_SYSTEM, $this->getSemanticDbSystem())
            ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, $params['url'])
            ->setAttribute(TraceAttributes::DB_NAME, $params['dbname'])
            ->setAttribute(TraceAttributes::DB_USER, $params['user'])
        ;

        $spanBuilder->setSpanKind(SpanKind::KIND_SERVER);

        $context = Context::getCurrent();

        $span = $spanBuilder->setParent($context)->startSpan();
        $span->storeInContext($context)->activate();

        return new Connection(parent::connect($params));
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
