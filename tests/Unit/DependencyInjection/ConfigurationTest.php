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
    public function testDefaultConfiguration(): void
    {
        $configuration = (new Processor())->processConfiguration(new Configuration(), ['open_telemetry' => []]);

        self::assertSame([
            'service' => [
                'environment' => '%kernel.environment%',
            ],
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

        $ouput = $dumper->dump(new Configuration());

        self::assertSame(<<<YML
        open_telemetry:
            service:
                namespace:            ~ # Required, Example: MyOrganization
                name:                 ~ # Required, Example: MyApp
                version:              ~ # Required, Example: 1.0.0
                environment:          '%kernel.environment%' # Required
            instrumentation:
                http_kernel:
                    enabled:              true

                    # The tracing provider to use, defaults to `default_provider`
                    tracing_provider:     ~
                console:
                    enabled:              true

                    # The tracing provider to use, defaults to `default_provider`
                    tracing_provider:     ~
            traces:
                enabled:              true

                # The default provider to use among the `providers`
                default_provider:     ~ # Required
                providers:

                    # Prototype
                    provider:
                        type:                 default # One of "default"; "noop", Required
                        sampler:
                            type:                 always_on # One of "always_on"; "always_off"; "trace_id_ratio"; "parent_based", Required
                        processor:            ~ # Required
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

        YML, $ouput);
    }
}
