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
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Attribute\DoctrineTraceAttributeEnum;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type OverrideParams from DriverManager
 * @phpstan-import-type Params from DriverManager
 */
final class TraceableDriverV4 extends AbstractDriverMiddleware
{
    public function __construct(
        private TracerInterface $tracer,
        DriverInterface $driver,
        private ?LoggerInterface $logger = null,
    ) {
        parent::__construct($driver);
    }

    /**
     * @param Params $params
     */
    public function connect(
        #[\SensitiveParameter]
        array $params,
    ): Connection {
        $scope = Context::storage()->scope();
        if (null !== $scope) {
            $this->logger?->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        } else {
            $this->logger?->debug('No active scope');
        }
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('doctrine.dbal.connection')
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setParent($scope?->context())
                ->setAttribute(TraceAttributes::DB_NAMESPACE, $params['dbname'] ?? 'default')
                ->setAttribute(DoctrineTraceAttributeEnum::User->toString(), $params['user'])
            ;

            $span = $spanBuilder->startSpan();

            $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

            $connection = parent::connect($params);

            $span->setAttribute(TraceAttributes::DB_SYSTEM_NAME, $this->getSemanticDbSystem($connection->getServerVersion()));
            $span->setStatus(StatusCode::STATUS_OK);

            return new TraceableConnection($connection, new Tracer($this->tracer, $this->logger));
        } catch (Exception $exception) {
            $span->recordException($exception);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            throw $exception;
        } finally {
            if ($span instanceof SpanInterface) {
                $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
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
