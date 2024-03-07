<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Doctrine\Middleware;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Exception;
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
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type OverrideParams from DriverManager
 * @phpstan-import-type Params from DriverManager
 */
final class TraceableDriverV3 extends AbstractDriverMiddleware
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
        array $params
    ): Connection {
        $scope = Context::storage()->scope();
        if (null !== $scope) {
            $this->logger?->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        }
        $span = null;

        try {
            $spanBuilder = $this->tracer
                ->spanBuilder('doctrine.dbal.connection')
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setParent($scope?->context())
                ->setAttribute(TraceAttributes::DB_SYSTEM, $this->getSemanticDbSystem())
                ->setAttribute(TraceAttributes::DB_CONNECTION_STRING, $params['url'] ?? $params['path'] ?? '')
                ->setAttribute(TraceAttributes::DB_NAME, $params['dbname'] ?? 'default')
                ->setAttribute(TraceAttributes::DB_USER, $params['user'])
            ;

            $span = $spanBuilder->startSpan();

            $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

            if (null === $scope) {
                $scope = $span->storeInContext(Context::getCurrent())->activate();
                $this->logger?->debug(sprintf('No active scope, activating new scope "%s"', spl_object_id($scope)));
            }

            $connection = parent::connect($params);

            $span->setStatus(StatusCode::STATUS_OK);

            return new TraceableConnection($connection, new Tracer($this->tracer, $this->logger));
        } catch (Exception $exception) {
            $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());

            throw $exception;
        } finally {
            if (null !== $scope) {
                $this->logger?->debug(sprintf('Detaching scope "%s"', spl_object_id($scope)));
                $scope->detach();
            }
            if ($span instanceof SpanInterface) {
                $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
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
