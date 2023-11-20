<?php

namespace GaelReyrol\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Definition\Processor;

/**
 * @coversDefaultClass \GaelReyrol\OpenTelemetryBundle\DependencyInjection\Configuration
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
                    'enabled' => true,
                    'request_headers' => [],
                    'response_headers' => [],
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
            'metrics' => [
                'enabled' => true,
                'meters' => [],
                'providers' => [],
                'exporters' => [],
            ],
            'logs' => [
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
                    request_headers:      []
                    response_headers:     []

                    # The meter to use, defaults to `default_meter`
                    meter:                ~
                console:
                    enabled:              true

                    # The tracer to use, defaults to `default_tracer`
                    tracer:               ~

                    # The meter to use, defaults to `default_meter`
                    meter:                ~
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

                        # Required if exporter type is json
                        format:               ~ # One of "json"; "ndjson"; "gprc"; "protobuf"
                        headers:

                            # Prototype
                            -
                                name:                 ~ # Required
                                value:                ~ # Required
                        compression:          ~ # One of "none"; "gzip"
            metrics:
                enabled:              true

                # The default meter to use among the `meters`
                default_meter:        ~ # Required
                meters:

                    # Prototype
                    meter:
                        name:                 ~
                        provider:             ~ # Required
                providers:

                    # Prototype
                    provider:
                        type:                 default # One of "noop"; "default", Required
                        exporter:             ~ # Required
                exporters:

                    # Prototype
                    exporter:
                        type:                 default # One of "noop"; "default"; "console"; "in_memory"; "push_default"; "push_console", Required
                        temporality:          ~ # One of "delta"; "cumulative"
                        protocol:             ~ # One of "noop"; "default"
            logs:
                enabled:              true

        YML, $output);
    }
}
