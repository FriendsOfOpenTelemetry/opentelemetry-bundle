<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\HttpKernel;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeEnum;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\InstrumentationTypeInterface;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\Framework\Routing\TraceableRouteLoader;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\OpenTelemetry\Context\Attribute\HttpKernelTraceAttributeEnum;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SemConv\TraceAttributes;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final class TraceableHttpKernelEventSubscriber implements EventSubscriberInterface, ServiceSubscriberInterface, InstrumentationTypeInterface
{
    private const REQUEST_ATTRIBUTE_SPAN = '__opentelemetry_symfony_internal_span';
    private const REQUEST_ATTRIBUTE_SCOPE = '__opentelemetry_symfony_internal_scope';
    private const REQUEST_ATTRIBUTE_EXCEPTION = '__opentelemetry_symfony_internal_exception';

    /**
     * @var array<string, ?string>
     */
    private array $requestHeaderAttributes;

    /**
     * @var array<string, ?string>
     */
    private array $responseHeaderAttributes;

    private InstrumentationTypeEnum $instrumentationType = InstrumentationTypeEnum::Auto;

    /**
     * @var string[]
     */
    private array $excludePaths = [];

    /**
     * @param iterable<string> $requestHeaders
     * @param iterable<string> $responseHeaders
     */
    public function __construct(
        private readonly TracerInterface $tracer,
        private readonly TextMapPropagatorInterface $propagator,
        private readonly PropagationGetterInterface $propagationGetter,
        /** @var ServiceLocator<TracerInterface> */
        private readonly ServiceLocator $tracerLocator,
        private readonly ?LoggerInterface $logger = null,
        iterable $requestHeaders = [],
        iterable $responseHeaders = [],
    ) {
        $this->requestHeaderAttributes = $this->createHeaderAttributeMapping('request', $requestHeaders);
        $this->responseHeaderAttributes = $this->createHeaderAttributeMapping('response', $responseHeaders);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['startRequest', 10000],
                ['recordRoute', 31], // after \Symfony\Component\HttpKernel\EventListener\RouterListener
            ],
            KernelEvents::CONTROLLER => [
                ['recordController'],
            ],
            KernelEvents::CONTROLLER_ARGUMENTS => [
                ['recordControllerArguments'],
            ],
            KernelEvents::VIEW => [
                ['recordView'],
            ],
            KernelEvents::RESPONSE => [
                ['recordResponse', -10000],
            ],
            KernelEvents::FINISH_REQUEST => [
                ['endScope', -10000],
                ['endRequest', -10000],
            ],
            KernelEvents::TERMINATE => [
                ['terminateRequest', 10000],
            ],
            KernelEvents::EXCEPTION => [
                ['recordException'],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [TracerInterface::class];
    }

    public function startRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (true === $event->isMainRequest() && false === $this->isAutoTraceable($request)) {
            return;
        }

        $span = $this->startSpan($event);
    }

    public function recordRoute(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $span = $this->fetchRequestSpan($request);

        if (null === $span && true === $this->isAttributeTraceable($request)) {
            $span = $this->startSpan($event);
        }

        if (null === $span) {
            return;
        }

        $routeName = $request->attributes->get('_route', '');
        if ('' === $routeName) {
            return;
        }

        $span->updateName($routeName);
        $span->setAttribute(TraceAttributes::HTTP_ROUTE, $routeName);
    }

    private function startSpan(RequestEvent $event): SpanInterface
    {
        $request = $event->getRequest();
        $tracer = $this->getTracer($request);

        $spanBuilder = $tracer
            ->spanBuilder(sprintf('HTTP %s', $request->getMethod()))
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setAttributes($this->requestAttributes($request))
            ->setAttributes($this->headerAttributes($request->headers, $this->requestHeaderAttributes))
        ;

        $parent = Context::getCurrent();

        if ($event->isMainRequest()) {
            $spanBuilder->setSpanKind(SpanKind::KIND_SERVER);
            $parent = $this->propagator->extract(
                $request,
                $this->propagationGetter,
                $parent,
            );

            $requestTime = $request->server->get('REQUEST_TIME_FLOAT');
            if (null !== $requestTime) {
                $spanBuilder->setStartTimestamp($requestTime * 1_000_000_000);
            }
        }

        $span = $spanBuilder->setParent($parent)->startSpan();

        $this->logger?->debug(sprintf('Starting span "%s"', $span->getContext()->getSpanId()));

        $scope = $span->storeInContext($parent)->activate();

        $request->attributes->set(self::REQUEST_ATTRIBUTE_SPAN, $span);
        $request->attributes->set(self::REQUEST_ATTRIBUTE_SCOPE, $scope);

        return $span;
    }

    public function recordController(ControllerEvent $event): void
    {
        $span = $this->fetchRequestSpan($event->getRequest());
        if (null === $span) {
            return;
        }
    }

    public function recordControllerArguments(ControllerArgumentsEvent $event): void
    {
        $span = $this->fetchRequestSpan($event->getRequest());
        if (null === $span) {
            return;
        }
    }

    public function recordView(ViewEvent $event): void
    {
        $span = $this->fetchRequestSpan($event->getRequest());
        if (null === $span) {
            return;
        }
    }

    public function recordException(ExceptionEvent $event): void
    {
        $span = $this->fetchRequestSpan($event->getRequest());
        if (null === $span) {
            return;
        }

        $span->recordException($event->getThrowable());
        $event->getRequest()->attributes->set(self::REQUEST_ATTRIBUTE_EXCEPTION, $event->getThrowable());
    }

    public function recordResponse(ResponseEvent $event): void
    {
        $span = $this->fetchRequestSpan($event->getRequest());
        if (null === $span) {
            return;
        }

        $event->getRequest()->attributes->remove(self::REQUEST_ATTRIBUTE_EXCEPTION);

        if (!$span->isRecording()) {
            return;
        }

        $response = $event->getResponse();
        $span->setAttribute(TraceAttributes::HTTP_RESPONSE_BODY_SIZE, $response->headers->get('Content-Length'));
        $span->setAttribute(TraceAttributes::NETWORK_PROTOCOL_VERSION, $response->getProtocolVersion());
        $span->setAttribute(TraceAttributes::HTTP_RESPONSE_STATUS_CODE, $response->getStatusCode());
        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            $span->setStatus(StatusCode::STATUS_ERROR);
        } else {
            $span->setStatus(StatusCode::STATUS_OK);
        }

        $span->setAttributes($this->headerAttributes($response->headers, $this->responseHeaderAttributes));
    }

    public function endScope(FinishRequestEvent $event): void
    {
        $scope = $this->fetchRequestScope($event->getRequest());
        if (null === $scope) {
            $this->logger?->debug('No active scope');

            return;
        }
        $this->logger?->debug(sprintf('Detaching scope "%s"', spl_object_id($scope)));
        $scope->detach();
    }

    public function endRequest(FinishRequestEvent $event): void
    {
        $span = $this->fetchRequestSpan($event->getRequest());
        if (null === $span) {
            return;
        }

        $exception = $this->fetchRequestException($event->getRequest());
        if (null !== $exception) {
            $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
        } elseif ($event->isMainRequest()) {
            // End span on ::terminateRequest() instead
            return;
        }

        $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
    }

    public function terminateRequest(TerminateEvent $event): void
    {
        $span = $this->fetchRequestSpan($event->getRequest());
        if (null === $span) {
            return;
        }

        $this->logger?->debug(sprintf('Ending span "%s"', $span->getContext()->getSpanId()));
        $span->end();
    }

    private function isAutoTraceable(Request $request): bool
    {
        if (InstrumentationTypeEnum::Auto !== $this->instrumentationType) {
            return false;
        }

        if (0 === count($this->excludePaths)) {
            return true;
        }

        $combinedExcludePaths = implode('|', $this->excludePaths);
        if (preg_match("#{$combinedExcludePaths}#", $request->getPathInfo())) {
            return false;
        }

        return true;
    }

    private function isAttributeTraceable(Request $request): bool
    {
        return InstrumentationTypeEnum::Attribute === $this->instrumentationType
            && true === $request->attributes->get(TraceableRouteLoader::DEFAULT_KEY, false);
    }

    private function getTracer(Request $request): TracerInterface
    {
        $tracer = $request->attributes->get(TraceableRouteLoader::TRACER_KEY);
        if (null !== $tracer) {
            return $this->tracerLocator->get($tracer);
        }

        return $this->tracer;
    }

    private function fetchRequestSpan(Request $request): ?SpanInterface
    {
        return $this->fetchRequestAttribute($request, self::REQUEST_ATTRIBUTE_SPAN, SpanInterface::class);
    }

    private function fetchRequestScope(Request $request): ?ScopeInterface
    {
        return $this->fetchRequestAttribute($request, self::REQUEST_ATTRIBUTE_SCOPE, ScopeInterface::class);
    }

    private function fetchRequestException(Request $request): ?\Throwable
    {
        return $this->fetchRequestAttribute($request, self::REQUEST_ATTRIBUTE_EXCEPTION, \Throwable::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @phpstan-return T|null
     */
    private function fetchRequestAttribute(Request $request, string $key, string $type): ?object
    {
        return ($object = $request->attributes->get($key)) instanceof $type ? $object : null;
    }

    /**
     * @return iterable<string, string>
     */
    private function requestAttributes(Request $request): iterable
    {
        return [
            TraceAttributes::URL_FULL => $request->getUri(),
            TraceAttributes::HTTP_REQUEST_METHOD => $request->getMethod(),
            TraceAttributes::URL_PATH => $request->getPathInfo(),
            HttpKernelTraceAttributeEnum::HttpHost->toString() => $request->getHttpHost(),
            TraceAttributes::URL_SCHEME => $request->getScheme(),
            TraceAttributes::NETWORK_PROTOCOL_VERSION => ($protocolVersion = $request->getProtocolVersion()) !== null
                ? strtr($protocolVersion, ['HTTP/' => ''])
                : null,
            TraceAttributes::USER_AGENT_ORIGINAL => $request->headers->get('User-Agent'),
            TraceAttributes::HTTP_REQUEST_BODY_SIZE => $request->headers->get('Content-Length'),
            TraceAttributes::NETWORK_PEER_ADDRESS => $request->getClientIp(),

            HttpKernelTraceAttributeEnum::NetPeerIp->toString() => $request->server->get('REMOTE_ADDR'),
            TraceAttributes::CLIENT_ADDRESS => $request->server->get('REMOTE_HOST'),
            TraceAttributes::CLIENT_PORT => $request->server->get('REMOTE_PORT'),
            HttpKernelTraceAttributeEnum::NetHostIp->toString() => $request->server->get('SERVER_ADDR'),
            TraceAttributes::SERVER_ADDRESS => $request->server->get('SERVER_NAME'),
            TraceAttributes::SERVER_PORT => $request->server->get('SERVER_PORT'),
        ];
    }

    /**
     * @param array<string> $headers
     *
     * @return \Generator<string, array<int, string>>
     */
    private function headerAttributes(HeaderBag $headerBag, array $headers): \Generator
    {
        foreach ($headers as $header => $attribute) {
            if ($headerBag->has($header)) {
                yield $attribute => $headerBag->all($header);
            }
        }
    }

    /**
     * @param iterable<string> $headers
     *
     * @return array<string, string>
     */
    private function createHeaderAttributeMapping(string $type, iterable $headers): array
    {
        $headerAttributes = [];
        foreach ($headers as $header) {
            $lcHeader = strtolower($header);
            $headerAttributes[$lcHeader] = sprintf('http.%s.header.%s', $type, strtr($lcHeader, ['-' => '_']));
        }

        return $headerAttributes;
    }

    public function setInstrumentationType(InstrumentationTypeEnum $type): void
    {
        $this->instrumentationType = $type;
    }

    /**
     * @param string[] $excludePaths
     */
    public function setExcludePaths(array $excludePaths): void
    {
        $this->excludePaths = $excludePaths;
    }
}
