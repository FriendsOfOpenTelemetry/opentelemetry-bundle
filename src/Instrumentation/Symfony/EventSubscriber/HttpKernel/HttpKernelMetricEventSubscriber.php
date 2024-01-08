<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Instrumentation\Symfony\EventSubscriber\HttpKernel;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class HttpKernelMetricEventSubscriber implements EventSubscriberInterface
{
    private CounterInterface $totalRequestsCounter;
    private CounterInterface $requestCounter;

    public function __construct(
        private readonly MeterInterface $meter,
        private readonly MeterProviderInterface $meterProvider,
    ) {
        $this->totalRequestsCounter = $this->meter->createCounter('symfony_http_kernel_requests');
        $this->requestCounter = $this->meter->createCounter('symfony_http_kernel_request');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['recordRequest', 10000],
                ['recordRoute', 31], // after RouterListener
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
                ['flushMeterProvider', -10000],
            ],
//            KernelEvents::TERMINATE => [],
            KernelEvents::EXCEPTION => [
                ['recordException'],
            ],
        ];
    }

    public function recordRequest(RequestEvent $event): void
    {
        $this->totalRequestsCounter->add(1);
    }

    public function recordRoute(RequestEvent $event): void
    {
    }

    public function recordController(ControllerEvent $event): void
    {
    }

    public function recordControllerArguments(ControllerArgumentsEvent $event): void
    {
    }

    public function recordView(ViewEvent $event): void
    {
    }

    public function recordException(ExceptionEvent $event): void
    {
    }

    public function recordResponse(ResponseEvent $event): void
    {
        $statusCode = $event->getResponse()->getStatusCode();

        $request = $event->getRequest();
        $method = $request->getMethod();
        $uri = $request->getRequestUri();
        $route = $request->attributes->get('_route', '');

        if ('_wdt' === $route) {
            return;
        }

        $this->requestCounter->add(1, [
            'method' => $method,
            'uri' => $uri,
            'route' => $route,
            'status' => $statusCode,
        ]);
    }

    public function flushMeterProvider(FinishRequestEvent $event): void
    {
        $this->meterProvider->forceFlush();
    }
}
