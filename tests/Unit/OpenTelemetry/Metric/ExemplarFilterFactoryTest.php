<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Unit\OpenTelemetry\Metric;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\ExemplarFilterEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Metric\ExemplarFilterFactory;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExemplarFilterFactory::class)]
class ExemplarFilterFactoryTest extends TestCase
{
    /**
     * @param ?array<int, mixed> $params
     */
    #[DataProvider('exemplarFilter')]
    public function testCreate(string $name, ?string $expectedClass, ?array $params = [], ?\Exception $exception = null): void
    {
        if ($exception instanceof \Exception) {
            self::expectExceptionObject($exception);
        }

        self::assertInstanceOf($expectedClass, ExemplarFilterFactory::create($name, $params));
    }

    /**
     * @return \Generator<string, array{
     *     0: string,
     *     1: ?class-string<ExemplarFilterInterface>,
     *     2?: array<int|string, mixed>,
     *     3?: ?\Exception
     * }>
     */
    public static function exemplarFilter(): \Generator
    {
        yield 'all' => [
            ExemplarFilterEnum::All->value,
            AllExemplarFilter::class,
        ];

        yield 'none' => [
            ExemplarFilterEnum::None->value,
            NoneExemplarFilter::class,
        ];

        yield 'with_sampled_trace' => [
            ExemplarFilterEnum::WithSampledTrace->value,
            WithSampledTraceExemplarFilter::class,
        ];

        $anonymousFilter = new class implements ExemplarFilterInterface {
            /** @phpstan-ignore-next-line */
            public function accepts(float|int $value, AttributesInterface $attributes, ContextInterface $context, int $timestamp): bool
            {
                return true;
            }
        };

        yield 'service' => [
            ExemplarFilterEnum::Service->value,
            ExemplarFilterInterface::class,
            [
                'service_id' => $anonymousFilter,
            ],
        ];

        yield 'service with stdClass' => [
            ExemplarFilterEnum::Service->value,
            ExemplarFilterInterface::class,
            [
                'service_id' => new \stdClass(),
            ],
            new \InvalidArgumentException('Parameter service_id must be an instance of ExemplarFilterInterface'),
        ];

        yield 'unknown' => [
            'unknown',
            null,
            [],
            new \InvalidArgumentException('Unknown exemplar filter: unknown'),
        ];
    }
}
