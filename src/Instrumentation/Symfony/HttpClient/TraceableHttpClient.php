<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpClient;

use Nyholm\Psr7\Uri;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

final class TraceableHttpClient implements HttpClientInterface, LoggerAwareInterface, ResetInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private readonly TracerInterface $tracer,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $scope = Context::storage()->scope();
        if (null !== $scope) {
            $this->logger?->debug(sprintf('Using scope "%s"', spl_object_id($scope)));
        } else {
            $this->logger?->debug('No active scope');
        }

        $uri = new Uri($url);

        $spanBuilder = $this->tracer
            ->spanBuilder('http.client')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setParent($scope?->context())
            ->setAttribute(TraceAttributes::URL_FULL, $url)
            ->setAttribute(TraceAttributes::URL_SCHEME, $uri->getScheme())
            ->setAttribute(TraceAttributes::URL_PATH, $uri->getPath())
            ->setAttribute(TraceAttributes::URL_QUERY, $uri->getQuery())
            ->setAttribute(TraceAttributes::URL_FRAGMENT, $uri->getFragment())
            ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $method)
        ;

        $span = $spanBuilder->startSpan();

        $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

        return new TraceableResponse($this->client, $this->client->request($method, $url, $options), $span);
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        if ($responses instanceof TraceableResponse) {
            $responses = [$responses];
        } elseif (!is_iterable($responses)) {
            throw new \TypeError(sprintf('"%s()" expects parameter 1 to be an iterable of TraceableResponse objects, "%s" given.', __METHOD__, get_debug_type($responses)));
        }

        return new ResponseStream(TraceableResponse::stream($this->client, $responses, $timeout));
    }

    /**
     * @param array<mixed> $options
     */
    public function withOptions(array $options): static
    {
        $clone = clone $this;
        $clone->client = $this->client->withOptions($options);

        return $clone;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        if ($this->client instanceof LoggerAwareInterface) {
            $this->client->setLogger($logger);
        }
        $this->logger = $logger;
    }

    public function reset(): void
    {
        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
    }
}
