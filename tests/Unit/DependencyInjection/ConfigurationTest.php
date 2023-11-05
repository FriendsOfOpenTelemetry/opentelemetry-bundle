<?php

namespace GaelReyrol\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use GaelReyrol\OpenTelemetryBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
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
}
