<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetryBundle;
use Nyholm\BundleTest\TestKernel;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\NoopLoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\NoopMeterProvider;
use OpenTelemetry\SDK\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', TestKernel::class)]
#[Env('SYMFONY_DEPRECATIONS_HELPER', 'max[self]=0&max[direct]=0&quiet[]=indirect&quiet[]=other')]
#[Env('APP_DEBUG', '0')]
class BundleInitializationTest extends KernelTestCase
{
    /**
     * @param array{
     *     environment?: string,
     *     debug?: bool,
     * }|array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(OpenTelemetryBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * @return string[]
     */
    private function getInstrumentationServices(): array
    {
        return [
            'open_telemetry.instrumentation.cache.trace.adapter',
            'open_telemetry.instrumentation.cache.trace.tag_aware_adapter',
            'open_telemetry.instrumentation.console.trace.event_subscriber',
            'open_telemetry.instrumentation.doctrine.trace.event_subscriber',
            'open_telemetry.instrumentation.doctrine.trace.middleware',
            'open_telemetry.instrumentation.http_client.trace.client',
            'open_telemetry.instrumentation.http_kernel.trace.event_subscriber',
            'open_telemetry.instrumentation.mailer.trace.event_subscriber',
            'open_telemetry.instrumentation.mailer.trace.default_transport',
            'open_telemetry.instrumentation.mailer.trace.mailer',
            'open_telemetry.instrumentation.messenger.trace.event_subscriber',
            'open_telemetry.instrumentation.messenger.trace.transport',
            'open_telemetry.instrumentation.messenger.trace.transport_factory',
            'messenger.transport.open_telemetry_tracer.factory',
            'open_telemetry.instrumentation.messenger.trace.middleware',
            'messenger.middleware.open_telemetry_tracer',
            'open_telemetry.instrumentation.twig.trace.extension',
        ];
    }

    public function testDefaultBundle(): void
    {
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/Fixtures/yml/default.yml');
        }]);

        $container = $kernel->getContainer();

        array_map(fn (string $parameter) => self::assertTrue($container->hasParameter($parameter)), [
            'open_telemetry.service.namespace',
            'open_telemetry.service.name',
            'open_telemetry.service.environment',
        ]);

        array_map(fn (array $parameter) => self::assertEquals($parameter['value'], $container->getParameter($parameter['name'])), [
            ['name' => 'open_telemetry.service.namespace', 'value' => 'FriendsOfOpenTelemetry/OpenTelemetry'],
            ['name' => 'open_telemetry.service.name', 'value' => 'Test'],
            ['name' => 'open_telemetry.service.version', 'value' => '0.0.0'],
            ['name' => 'open_telemetry.service.environment', 'value' => 'test'],
        ]);

        $privateContainer = self::getContainer();

        foreach ($this->getInstrumentationServices() as $serviceId) {
            self::assertFalse($privateContainer->has($serviceId));
        }
    }

    public function testNoop(): void
    {
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/Fixtures/yml/noop.yml');
        }]);

        $publicContainer = $kernel->getContainer();
        $privateContainer = self::getContainer();

        self::assertTrue($publicContainer->has('open_telemetry.traces.tracers.main'));
        self::assertTrue($privateContainer->has('open_telemetry.traces.tracers.main'));
        $tracer = $privateContainer->get('open_telemetry.traces.tracers.main');
        self::assertInstanceOf(TracerInterface::class, $tracer);

        self::assertFalse($publicContainer->has('open_telemetry.traces.providers.noop'));
        self::assertTrue($privateContainer->has('open_telemetry.traces.providers.noop'));
        $traceProvider = $privateContainer->get('open_telemetry.traces.providers.noop');
        self::assertInstanceOf(NoopTracerProvider::class, $traceProvider);

        self::assertTrue($publicContainer->has('open_telemetry.metrics.meters.main'));
        self::assertTrue($privateContainer->has('open_telemetry.metrics.meters.main'));
        $meter = $privateContainer->get('open_telemetry.metrics.meters.main');
        self::assertInstanceOf(MeterInterface::class, $meter);

        self::assertFalse($publicContainer->has('open_telemetry.metrics.providers.noop'));
        self::assertTrue($privateContainer->has('open_telemetry.metrics.providers.noop'));
        $metricProvider = $privateContainer->get('open_telemetry.metrics.providers.noop');
        self::assertInstanceOf(NoopMeterProvider::class, $metricProvider);

        self::assertTrue($publicContainer->has('open_telemetry.logs.loggers.main'));
        self::assertTrue($privateContainer->has('open_telemetry.logs.loggers.main'));
        $logger = $privateContainer->get('open_telemetry.logs.loggers.main');
        self::assertInstanceOf(LoggerInterface::class, $logger);

        self::assertFalse($publicContainer->has('open_telemetry.logs.providers.noop'));
        self::assertTrue($privateContainer->has('open_telemetry.logs.providers.noop'));
        $loggerProvider = $privateContainer->get('open_telemetry.logs.providers.noop');
        self::assertInstanceOf(NoopLoggerProvider::class, $loggerProvider);
    }

    public function testTracesDefaultSimpleOtlp(): void
    {
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/Fixtures/yml/traces-default-simple-otlp.yml');
        }]);

        $publicContainer = $kernel->getContainer();
        $privateContainer = self::getContainer();

        self::assertTrue($publicContainer->has('open_telemetry.traces.tracers.main'));
        self::assertTrue($privateContainer->has('open_telemetry.traces.tracers.main'));
        $tracer = $privateContainer->get('open_telemetry.traces.tracers.main');
        self::assertInstanceOf(TracerInterface::class, $tracer);

        self::assertFalse($publicContainer->has('open_telemetry.traces.providers.default'));
        self::assertTrue($privateContainer->has('open_telemetry.traces.providers.default'));
        $provider = $privateContainer->get('open_telemetry.traces.providers.default');
        self::assertInstanceOf(TracerProvider::class, $provider);

        self::assertFalse($publicContainer->has('open_telemetry.traces.processors.simple'));
        self::assertTrue($privateContainer->has('open_telemetry.traces.processors.simple'));
        $processor = $privateContainer->get('open_telemetry.traces.processors.simple');
        self::assertInstanceOf(SimpleSpanProcessor::class, $processor);

        self::assertFalse($publicContainer->has('open_telemetry.traces.exporters.otlp'));
        self::assertTrue($privateContainer->has('open_telemetry.traces.exporters.otlp'));
        $exporter = $privateContainer->get('open_telemetry.traces.exporters.otlp');
        self::assertInstanceOf(SpanExporter::class, $exporter);
    }

    public function testLogsDefaultSimpleOtlp(): void
    {
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/Fixtures/yml/logs-default-simple-otlp.yml');
        }]);

        $publicContainer = $kernel->getContainer();
        $privateContainer = self::getContainer();

        self::assertTrue($publicContainer->has('open_telemetry.logs.loggers.main'));
        self::assertTrue($privateContainer->has('open_telemetry.logs.loggers.main'));
        $logger = $privateContainer->get('open_telemetry.logs.loggers.main');
        self::assertInstanceOf(LoggerInterface::class, $logger);

        self::assertFalse($publicContainer->has('open_telemetry.logs.providers.default'));
        self::assertTrue($privateContainer->has('open_telemetry.logs.providers.default'));
        $provider = $privateContainer->get('open_telemetry.logs.providers.default');
        self::assertInstanceOf(LoggerProvider::class, $provider);

        self::assertFalse($publicContainer->has('open_telemetry.logs.processors.simple'));
        self::assertTrue($privateContainer->has('open_telemetry.logs.processors.simple'));
        $processor = $privateContainer->get('open_telemetry.logs.processors.simple');
        self::assertInstanceOf(SimpleLogRecordProcessor::class, $processor);

        self::assertFalse($publicContainer->has('open_telemetry.logs.exporters.otlp'));
        self::assertTrue($privateContainer->has('open_telemetry.logs.exporters.otlp'));
        $exporter = $privateContainer->get('open_telemetry.logs.exporters.otlp');
        self::assertInstanceOf(LogsExporter::class, $exporter);
    }

    public function testMetricsDefaultOtlp(): void
    {
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestConfig(__DIR__.'/Fixtures/yml/metrics-default-otlp.yml');
        }]);

        $publicContainer = $kernel->getContainer();
        $privateContainer = self::getContainer();

        self::assertTrue($publicContainer->has('open_telemetry.metrics.meters.main'));
        self::assertTrue($privateContainer->has('open_telemetry.metrics.meters.main'));
        $meter = $privateContainer->get('open_telemetry.metrics.meters.main');
        self::assertInstanceOf(MeterInterface::class, $meter);

        self::assertFalse($publicContainer->has('open_telemetry.metrics.providers.default'));
        self::assertTrue($privateContainer->has('open_telemetry.metrics.providers.default'));
        $provider = $privateContainer->get('open_telemetry.metrics.providers.default');
        self::assertInstanceOf(MeterProvider::class, $provider);

        self::assertFalse($publicContainer->has('open_telemetry.metrics.exporters.otlp'));
        self::assertTrue($privateContainer->has('open_telemetry.metrics.exporters.otlp'));
        $exporter = $privateContainer->get('open_telemetry.metrics.exporters.otlp');
        self::assertInstanceOf(MetricExporter::class, $exporter);
    }
}
