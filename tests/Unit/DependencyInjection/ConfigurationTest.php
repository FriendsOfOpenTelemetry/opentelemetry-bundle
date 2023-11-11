<?php

namespace GaelReyrol\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Definition\Processor;

/**
 * @coversDefaultClass \GaelReyrol\OpenTelemetryBundle\DependencyInjection\Configuration
 */
final class ConfigurationTest extends TestCase
{
    /**
     * @param array<string, array<string,mixed>> $configs
     *
     * @return array<string, array<string,mixed>>
     */
    protected function process(array $configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }

    public function testEmptyConfiguration(): void
    {
        $configuration = $this->process([]);

        self::assertSame([
            'service' => [],
            'instrumentation' => [
                'http_kernel' => [
                    'enabled' => true,
                ],
                'console' => [
                    'enabled' => true,
                ],
            ],
            'traces' => [
                'enabled' => true,
                'tracers' => [],
                'providers' => [],
                'processors' => [],
                'exporters' => [],
            ],
            'logs' => [
                'enabled' => true,
            ],
            'metrics' => [
                'enabled' => true,
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
                    enabled:              true

                    # The tracer to use, defaults to `default_tracer`
                    tracer:               ~
                console:
                    enabled:              true

                    # The tracer to use, defaults to `default_tracer`
                    tracer:               ~
            traces:
                enabled:              true

                # The default tracer to use among the `tracers`
                default_tracer:       ~ # Required
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
                        processors:           [] # Required
                processors:

                    # Prototype
                    processor:
                        type:                 simple # One of "noop"; "simple"; "multi", Required

                        # Required if processor type is multi
                        processors:           []

                        # Required if processor type is simple or batch
                        exporter:             ~
                exporters:

                    # Prototype
                    exporter:
                        type:                 otlp # One of "in_memory"; "console"; "otlp"; "zipkin", Required
                        endpoint:             ~ # Required

                        # Required if exporter type is otlp
                        format:               ~ # One of "json"; "ndjson"; "gprc"; "protobuf"
                        headers:

                            # Prototype
                            -
                                name:                 ~ # Required
                                value:                ~ # Required
                        compression:          ~ # One of "none"; "gzip"
            logs:
                enabled:              true
            metrics:
                enabled:              true

        YML, $output);
    }
}
