<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\HttpClientTracingPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(HttpClientTracingPass::class)]
class HttpClientTracingPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new HttpClientTracingPass());

        $container->setDefinition('open_telemetry.instrumentation.http_client.trace.client', new Definition());
    }

    public function testRemoveInstrumentationByDefault(): void
    {
        $this->compile();

        self::assertContainerBuilderNotHasService('open_telemetry.instrumentation.http_client.trace.client');
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
    }
}
