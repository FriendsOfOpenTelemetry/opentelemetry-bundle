<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SamplerFactory::class)]
class SamplerFactoryTest extends TestCase
{
    #[DataProvider('samplerProvider')]
    public function testCreateSampler(string $name, string $expectedClass, string $description, ?float $probability): void
    {
        $sampler = SamplerFactory::create($name, $probability);

        self::assertInstanceOf($expectedClass, $sampler);
        self::assertSame($description, $sampler->getDescription());
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: class-string<SamplerInterface>,
     *     2: string,
     *     3: float|null,
     * }>
     */
    public static function samplerProvider(): \Generator
    {
        yield [
            'always_off',
            AlwaysOffSampler::class,
            'AlwaysOffSampler',
            null,
        ];

        yield [
            'always_on',
            AlwaysOnSampler::class,
            'AlwaysOnSampler',
            null,
        ];

        yield [
            'parent_based_always_off',
            ParentBased::class,
            'ParentBased+AlwaysOffSampler',
            null,
        ];

        yield [
            'parent_based_always_on',
            ParentBased::class,
            'ParentBased+AlwaysOnSampler',
            null,
        ];

        yield [
            'parent_based_trace_id_ratio',
            ParentBased::class,
            'ParentBased+TraceIdRatioBasedSampler{0.600000}',
            0.6,
        ];

        yield [
            'trace_id_ratio',
            TraceIdRatioBasedSampler::class,
            'TraceIdRatioBasedSampler{0.200000}',
            0.2,
        ];
    }
}
