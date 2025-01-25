<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpClientInstrumentationPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(HttpClientInstrumentationPass::class)]
class HttpClientInstrumentationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->setParameter('open_telemetry.instrumentation.http_client.tracing.enabled', false);
        $container->setDefinition('open_telemetry.instrumentation.http_client.trace.client', new Definition());

        $container->addCompilerPass(new HttpClientInstrumentationPass());
    }

    public function testNoInstrumentationByDefault(): void
    {
        $this->registerService('http_client', HttpClient::class)
            ->setPublic(true);
        $this->registerService('http_client.transport', HttpClientInterface::class)
            ->setPublic(true);

        $this->compile();

        $httpClient = $this->container->getDefinition('http_client');
        self::assertNull($httpClient->getDecoratedService());
        $httpClientTransport = $this->container->getDefinition('http_client.transport');
        self::assertNull($httpClientTransport->getDecoratedService());
    }

    public function testDoesNotRemoveInstrumentationWithClient(): void
    {
        $this->setParameter('open_telemetry.instrumentation.http_client.tracing.enabled', true);

        $this->registerService('http_client', HttpClient::class)
            ->setPublic(true);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.instrumentation.http_client.trace.client',
            '$client',
            new Reference('.inner'),
        );
        self::assertContainerBuilderServiceDecoration(
            'open_telemetry.instrumentation.http_client.trace.client',
            'http_client',
            null,
            15
        );
    }

    public function testDoesNotRemoveInstrumentationWithClientTransport(): void
    {
        $this->setParameter('open_telemetry.instrumentation.http_client.tracing.enabled', true);

        $this->registerService('http_client.transport', HttpClientInterface::class)
            ->setPublic(true);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'open_telemetry.instrumentation.http_client.trace.client',
            '$client',
            new Reference('.inner'),
        );
        self::assertContainerBuilderServiceDecoration(
            'open_telemetry.instrumentation.http_client.trace.client',
            'http_client.transport',
            null,
            -15
        );
    }
}
