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
                'cache' => [
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
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
                'doctrine' => [
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
                'http_client' => [
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
                'http_kernel' => [
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
                'mailer' => [
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
                'messenger' => [
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
                    ],
                    'metering' => [
                        'enabled' => false,
                    ],
                ],
                'twig' => [
                    'tracing' => [
                        'enabled' => false,
                        'request_headers' => [],
                        'response_headers' => [],
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
                cache:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
                console:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
                doctrine:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
                http_client:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
                http_kernel:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
                mailer:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
                messenger:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
                twig:
                    tracing:
                        enabled:              false

                        # The tracer to use, defaults to `traces.default_tracer` or first tracer in `traces.tracers`
                        tracer:               ~
                        request_headers:      []
                        response_headers:     []
                    metering:
                        enabled:              false

                        # The meter to use, defaults to `metrics.default_meter` or first meter in `metrics.meters`
                        meter:                ~
            traces:
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
                            type:                 always_on # One of "always_off"; "always_on"; "parent_based"; "trace_id_ratio", Required
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
                        dsn:                  ~ # Required
                        options:
                            format:               json # One of "json"; "ndjson"; "gprc"; "protobuf"
                            compression:          none # One of "gzip"; "none"
                            headers:

                                # Prototype
                                -
                                    name:                 ~ # Required
                                    value:                ~ # Required
                            timeout:              0.1
                            retry:                100
                            max:                  3
                            ca:                   ~
                            cert:                 ~
                            key:                  ~
            metrics:
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
                        filter:               none # One of "all"; "none"; "with_sampled_trace"
                exporters:

                    # Prototype
                    exporter:
                        dsn:                  ~ # Required
                        temporality:          delta # One of "delta"; "cumulative"; "low_memory"
                        options:
                            format:               json # One of "json"; "ndjson"; "gprc"; "protobuf"
                            compression:          none # One of "gzip"; "none"
                            headers:

                                # Prototype
                                -
                                    name:                 ~ # Required
                                    value:                ~ # Required
                            timeout:              0.1
                            retry:                100
                            max:                  3
                            ca:                   ~
                            cert:                 ~
                            key:                  ~
            logs:
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
                        type:                 simple # One of "multi"; "noop"; "simple", Required

                        # Required if processor type is multi
                        processors:           []

                        # Required if processor type is simple or batch
                        exporter:             ~
                exporters:

                    # Prototype
                    exporter:
                        dsn:                  ~ # Required
                        options:
                            format:               json # One of "json"; "ndjson"; "gprc"; "protobuf"
                            compression:          none # One of "gzip"; "none"
                            headers:

                                # Prototype
                                -
                                    name:                 ~ # Required
                                    value:                ~ # Required
                            timeout:              0.1
                            retry:                100
                            max:                  3
                            ca:                   ~
                            cert:                 ~
                            key:                  ~

        YML, $output);
    }
}
