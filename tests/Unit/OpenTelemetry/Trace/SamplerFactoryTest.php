<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Trace;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\Sampler\AttributeBasedSampler;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Trace\SamplerFactory;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SamplerFactory::class)]
class SamplerFactoryTest extends TestCase
{
    /**
     * @param array<int, mixed> $params
     */
    #[DataProvider('samplerProvider')]
    public function testCreateSampler(string $name, string $expectedClass, string $description, array $params = [], ?\Exception $exception = null): void
    {
        if ($exception instanceof \Exception) {
            self::expectExceptionObject($exception);
        }

        $sampler = SamplerFactory::create($name, $params);

        self::assertInstanceOf($expectedClass, $sampler);
        self::assertSame($description, $sampler->getDescription());
    }

    /**
     * @return \Generator<array{
     *     0: string,
     *     1: class-string<SamplerInterface>,
     *     2: string,
     *     3?: array<int|string, mixed>,
     *     4?: ?\Exception
     * }>
     */
    public static function samplerProvider(): \Generator
    {
        yield [
            'always_off',
            AlwaysOffSampler::class,
            'AlwaysOffSampler',
        ];

        yield [
            'always_on',
            AlwaysOnSampler::class,
            'AlwaysOnSampler',
        ];

        yield [
            'parent_based_always_off',
            ParentBased::class,
            'ParentBased+AlwaysOffSampler',
        ];

        yield [
            'parent_based_always_on',
            ParentBased::class,
            'ParentBased+AlwaysOnSampler',
        ];

        yield [
            'parent_based_trace_id_ratio',
            ParentBased::class,
            'ParentBased+TraceIdRatioBasedSampler{0.600000}',
            [0.6],
        ];

        yield [
            'trace_id_ratio',
            TraceIdRatioBasedSampler::class,
            'TraceIdRatioBasedSampler{0.200000}',
            [0.2],
        ];

        yield [
            'attribute_based',
            AttributeBasedSampler::class,
            'AttributeBasedSampler{traceable, 1}',
            ['traceable', true],
        ];

        $anonymousSampler = new class implements SamplerInterface {
            /** @phpstan-ignore-next-line */
            public function shouldSample(
                ContextInterface $parentContext,
                string $traceId,
                string $spanName,
                int $spanKind,
                AttributesInterface $attributes,
                array $links,
            ): SamplingResult {
                return new SamplingResult(SamplingResult::RECORD_AND_SAMPLE);
            }

            public function getDescription(): string
            {
                return 'AnonymousClassSampler';
            }
        };

        yield [
            'service',
            SamplerInterface::class,
            'AnonymousClassSampler',
            [
                'service_id' => $anonymousSampler,
            ],
        ];

        yield [
            'service',
            SamplerInterface::class,
            '',
            [
                'service_id' => new \stdClass(),
            ],
            new \InvalidArgumentException('Parameter service_id must be an instance of SamplerInterface'),
        ];

        yield [
            'unknown',
            SamplerInterface::class,
            '',
            [],
            new \InvalidArgumentException('Unknown sampler: unknown'),
        ];
    }
}
