<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpClient;

use Nyholm\Psr7\Uri;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use Symfony\Contracts\Service\ResetInterface;

final class TraceableHttpClient implements HttpClientInterface, LoggerAwareInterface, ResetInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private readonly TracerInterface $tracer,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            return $this->client->request($method, $url, $options);
        }

        $uri = new Uri($url);

        $spanBuilder = $this->tracer
            ->spanBuilder('http.client')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttribute(TraceAttributes::URL_FULL, $url)
            ->setAttribute(TraceAttributes::URL_SCHEME, $uri->getScheme())
            ->setAttribute(TraceAttributes::URL_PATH, $uri->getPath())
            ->setAttribute(TraceAttributes::URL_QUERY, $uri->getQuery())
            ->setAttribute(TraceAttributes::URL_FRAGMENT, $uri->getFragment())
            ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $method)
        ;

        $span = $spanBuilder->setParent(Context::getCurrent())->startSpan();

        try {
            return new TraceableResponse($this->client, $this->client->request($method, $url, $options), $span);
        } catch (ExceptionInterface $exception) {
            $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            throw $exception;
        } finally {
            $span->end();
        }
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        $scope = Context::storage()->scope();
        if (null === $scope) {
            return $this->client->stream($responses, $timeout);
        }

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
    }

    public function reset(): void
    {
        if ($this->client instanceof ResetInterface) {
            $this->client->reset();
        }
    }
}
