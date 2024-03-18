<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\DependencyInjection\Compiler;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\DependencyInjection\Compiler\SetInstrumentationTypePass;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeEnum;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(SetInstrumentationTypePass::class)]
class SetInstrumentationTypePassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetInstrumentationTypePass());

        $container->setDefinition('open_telemetry.instrumentation.console.trace.event_subscriber', new Definition());
        $container->setDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber', new Definition());
    }

    public function testNoInstrumentationType(): void
    {
        $this->compile();

        $console = $this->container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
        self::assertEquals([], $console->getMethodCalls());

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        self::assertEquals([], $httpKernel->getMethodCalls());
    }

    public function testInstrumentationType(): void
    {
        $this->container->setParameter('open_telemetry.instrumentation.console.type', InstrumentationTypeEnum::Attribute);
        $this->container->setParameter('open_telemetry.instrumentation.http_kernel.type', InstrumentationTypeEnum::Attribute);

        $this->compile();

        $console = $this->container->getDefinition('open_telemetry.instrumentation.console.trace.event_subscriber');
        self::assertEquals([['setInstrumentationType', [InstrumentationTypeEnum::Attribute]]], $console->getMethodCalls());

        $httpKernel = $this->container->getDefinition('open_telemetry.instrumentation.http_kernel.trace.event_subscriber');
        self::assertEquals([['setInstrumentationType', [InstrumentationTypeEnum::Attribute]]], $httpKernel->getMethodCalls());
    }
}
