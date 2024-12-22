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
        $container->addCompilerPass(new HttpClientInstrumentationPass());

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
