<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Definition\Processor;

/**
 * @coversDefaultClass \FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Configuration
 */
final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    /**
     * @param array<string, array<string,mixed>> $configs
     *
     * @return array<string, array<string,mixed>>
     */
    protected function process(array $configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration($this->getConfiguration(), $configs);
    }

    public function testEmptyConfiguration(): void
    {
        $configuration = $this->process([]);

        self::assertSame([
            'service' => [],
            'instrumentation' => [
                'http_kernel' => [
                    'enabled' => false,
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
                'console' => [
                    'enabled' => false,
                    'tracing' => [
                        'enabled' => false,
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
            ],
            'traces' => [
                'tracers' => [],
                'providers' => [],
                'processors' => [],
                'exporters' => [],
            ],
            'metrics' => [
                'meters' => [],
                'providers' => [],
                'exporters' => [],
            ],
            'logs' => [
                'monolog' => [
                    'enabled' => false,
                    'handlers' => [],
                ],
                'loggers' => [],
                'providers' => [],
                'processors' => [],
                'exporters' => [],
            ],
        ], $configuration);
    }

    public function testReferenceConfiguration(): void
    {
        $dumper = new YamlReferenceDumper();

        $output = $dumper->dump(new Configuration());

        self::assertSame(<<<YML
        open_telemetry:
            service:
                namespace:            ~ # Required, Example: MyOrganization
                name:                 ~ # Required, Example: MyApp
                version:              ~ # Required, Example: 1.0.0
                environment:          ~ # Required, Example: '%kernel.environment%'
            instrumentation:
                http_kernel:
                    enabled:              false
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~ # Required
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~ # Required
                console:
                    enabled:              false
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~ # Required
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~ # Required
            traces:

                # The default tracer to use among the `tracers`
                default_tracer:       ~
                tracers:

                    # Prototype
                    tracer:
                        name:                 ~
                        version:              ~
                        provider:             ~ # Required
                providers:

                    # Prototype
                    provider:
                        type:                 default # One of "default"; "noop", Required
                        sampler:
                            type:                 always_on # One of "always_on"; "always_off"; "trace_id_ratio"; "parent_based", Required
                            ratio:                ~
                            parent:               ~
                        processors:           []
                processors:

                    # Prototype
                    processor:
                        type:                 simple # One of "multi"; "simple"; "noop", Required

                        # Required if processor type is multi
                        processors:           []

                        # Required if processor type is simple or batch
                        exporter:             ~
                exporters:

                    # Prototype
                    exporter:
                        type:                 otlp # One of "in_memory"; "console"; "otlp"; "zipkin", Required
                        endpoint:             ~

                        # Required if exporter type is json
                        format:               ~ # One of "json"; "ndjson"; "gprc"; "protobuf"
                        headers:

                            # Prototype
                            -
                                name:                 ~ # Required
                                value:                ~ # Required
                        compression:          ~ # One of "none"; "gzip"
            metrics:

                # The default meter to use among the `meters`
                default_meter:        ~
                meters:

                    # Prototype
                    meter:
                        name:                 ~
                        provider:             ~ # Required
                providers:

                    # Prototype
                    provider:
                        type:                 default # One of "noop"; "default", Required
                        exporter:             ~
                        filter:               none # One of "with_sampled_trace"; "all"; "none"
                exporters:

                    # Prototype
                    exporter:
                        type:                 otlp # One of "noop"; "otlp"; "console"; "in_memory", Required
                        endpoint:             ~

                        # Required if exporter type is json
                        format:               ~ # One of "json"; "ndjson"; "gprc"; "protobuf"
                        headers:

                            # Prototype
                            -
                                name:                 ~ # Required
                                value:                ~ # Required
                        compression:          ~ # One of "none"; "gzip"
                        temporality:          ~ # One of "delta"; "cumulative"; "lowmemory"
            logs:

                # The default logger to use among the `loggers`
                default_logger:       ~
                monolog:
                    enabled:              false
                    handlers:

                        # Prototype
                        handler:
                            provider:             ~ # Required
                            level:                debug # One of "debug"; "info"; "notice"; "warning"; "error"; "critical"; "alert"; "emergency"
                            bubble:               true
                loggers:

                    # Prototype
                    logger:
                        name:                 ~
                        version:              ~
                        provider:             ~ # Required
                providers:

                    # Prototype
                    provider:
                        type:                 default # One of "default"; "noop", Required
                        processor:            ~
                processors:

                    # Prototype
                    processor:
                        type:                 simple # One of "multi"; "simple"; "noop", Required

                        # Required if processor type is multi
                        processors:           []

                        # Required if processor type is simple or batch
                        exporter:             ~
                exporters:

                    # Prototype
                    exporter:
                        type:                 otlp # One of "otlp"; "noop"; "console"; "in_memory", Required
                        endpoint:             ~

                        # Required if exporter type is json
                        format:               ~ # One of "json"; "ndjson"; "gprc"; "protobuf"
                        headers:

                            # Prototype
                            -
                                name:                 ~ # Required
                                value:                ~ # Required
                        compression:          ~ # One of "none"; "gzip"

        YML, $output);
    }
}
