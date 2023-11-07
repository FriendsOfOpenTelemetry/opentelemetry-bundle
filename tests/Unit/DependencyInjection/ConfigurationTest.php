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
                    'provider' => 'default_provider',
                ],
                'console' => [
                    'enabled' => true,
                    'provider' => 'default_provider',
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
                    provider:             default_provider # Required
                console:
                    enabled:              true
                    provider:             default_provider # Required
            traces:
                enabled:              true
                providers:

                    # Prototype
                    provider:
                        type:                 default # One of "default"; "noop"; "traceable", Required
                        sampler:
                            type:                 always_on # One of "always_on"; "always_off"; "trace_id_ratio"; "parent_based", Required
                        processor:            ~ # Required
                processors:

                    # Prototype
                    processor:
                        type:                 simple # One of "batch"; "multi"; "simple"; "noop", Required

                        # Required if processor type is multi
                        processors:           []

                        # Required if processor type is simple or batch
                        exporter:             ~
                exporters:

                    # Prototype
                    exporter:
                        type:                 otlp # One of "in_memory"; "stream"; "otlp"; "grpc"; "zipkin", Required
                        dsn:                  ~ # Required
            logs:
                enabled:              true
            metrics:
                enabled:              true

        YML, $ouput);
    }
}
