<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Configuration;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\OpenTelemetryExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

#[CoversClass(Configuration::class)]
final class ConfigurationFormatTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension(): ExtensionInterface
    {
        return new OpenTelemetryExtension();
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function testDefaultCompatibility(): void
    {
        $expectedConfiguration = [
            'service' => [
                'namespace' => 'FriendsOfOpenTelemetry/OpenTelemetry',
                'name' => 'Test',
                'version' => '0.0.0',
                'environment' => 'test',
            ],
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
        ];

        $sources = [
            __DIR__.'/../Fixtures/yml/default.yml',
        ];

        $this->assertProcessedConfigurationEquals($expectedConfiguration, $sources);
    }
}
