<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\HttpKernel;

use App\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\LoggingTestCaseTrait;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use Monolog\Level;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class HttpKernelAttributeTracingTest extends WebTestCase
{
    use TracingTestCaseTrait;
    use LoggingTestCaseTrait;

    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ok');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'app_traceable_actiontraceable_ok');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/ok',
            'http.request.method' => 'GET',
            'url.path' => '/ok',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_actiontraceable_ok',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testFailure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/failure');

        self::assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $client->getResponse()->getStatusCode());
        static::assertSame('{"status":"failure"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'app_traceable_actiontraceable_failure');
        self::assertSpanStatus($mainSpan, StatusData::error());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/failure',
            'http.request.method' => 'GET',
            'url.path' => '/failure',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_actiontraceable_failure',
            'http.response.status_code' => Response::HTTP_SERVICE_UNAVAILABLE,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testException(): void
    {
        $client = static::createClient();
        $client->request('GET', '/exception');

        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $client->getResponse()->getStatusCode());

        self::assertSpansCount(2);

        $spans = self::getSpans();

        $mainSpan = $spans[array_key_last($spans)];
        self::assertSpanName($mainSpan, 'app_traceable_actiontraceable_exception');
        self::assertSpanStatus($mainSpan, StatusData::error());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/exception',
            'http.request.method' => 'GET',
            'url.path' => '/exception',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_actiontraceable_exception',
            'http.response.status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ]);
        self::assertSpanEventsCount($mainSpan, 1);

        $exception = $mainSpan->getEvents()[0];
        self::assertSpanEventName($exception, 'exception');
        self::assertSpanEventAttributesSubSet($exception, [
            'exception.type' => 'RuntimeException',
            'exception.message' => 'Oops',
        ]);
    }

    public function testTraceableClassInvoke(): void
    {
        $client = static::createClient();
        $client->request('GET', '/class-invoke-traceable');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'app_traceable_classinvoketraceable__invoke');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/class-invoke-traceable',
            'http.request.method' => 'GET',
            'url.path' => '/class-invoke-traceable',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_classinvoketraceable__invoke',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testTraceableClass(): void
    {
        $client = static::createClient();
        $client->request('GET', '/class-traceable');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'app_traceable_classtraceable_index');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/class-traceable',
            'http.request.method' => 'GET',
            'url.path' => '/class-traceable',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_classtraceable_index',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testManualClass(): void
    {
        $client = static::createClient();
        $client->request('GET', '/class-manual');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(2);

        $manualSpan = self::getSpans()[0];
        self::assertSpanName($manualSpan, 'Manual');
        self::assertSpanStatus($manualSpan, StatusData::ok());
        self::assertSpanAttributes($manualSpan, [
            'code.function.name' => 'App\Controller\Traceable\ClassTraceableController::manual',
        ]);
        self::assertSpanEventsCount($manualSpan, 1);
        $manualSpanEvent = $manualSpan->getEvents()[0];
        self::assertSpanEventName($manualSpanEvent, 'sleep');
        self::assertSpanEventAttributes($manualSpanEvent, [
            'sleep.duration' => '1s',
        ]);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'app_traceable_classtraceable_manual');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/class-manual',
            'http.request.method' => 'GET',
            'url.path' => '/class-manual',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_classtraceable_manual',
            'http.response.status_code' => Response::HTTP_OK,
        ]);

        self::assertSame($manualSpan->getParentSpanId(), $mainSpan->getSpanId());
    }

    public function testNotTraceable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/not-traceable');

        self::assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        self::assertSpansCount(0);
    }

    public function testNotTraceableClass(): void
    {
        $client = static::createClient();
        $client->request('GET', '/not-traceable-class');

        self::assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        self::assertSpansCount(0);
    }

    public function testTraceableFallback(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fallback-traceable');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(0);

        self::assertSpansCount(1, 'open_telemetry.traces.exporters.fallback');

        $mainSpan = self::getSpans('open_telemetry.traces.exporters.fallback')[0];
        self::assertSpanName($mainSpan, 'app_traceable_fallbacktraceable__invoke');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/fallback-traceable',
            'http.request.method' => 'GET',
            'url.path' => '/fallback-traceable',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_fallbacktraceable__invoke',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testManualTrace(): void
    {
        $client = static::createClient();
        $client->request('GET', '/manual-action');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(2);

        $manualSpan = self::getSpans()[0];
        self::assertSpanName($manualSpan, 'Manual');
        self::assertSpanStatus($manualSpan, StatusData::ok());
        self::assertSpanAttributes($manualSpan, [
            'code.function.name' => 'App\Controller\Traceable\ActionTraceableController::manual',
        ]);
        self::assertSpanEventsCount($manualSpan, 1);
        $manualSpanEvent = $manualSpan->getEvents()[0];
        self::assertSpanEventName($manualSpanEvent, 'sleep');
        self::assertSpanEventAttributes($manualSpanEvent, [
            'sleep.duration' => '1s',
        ]);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'app_traceable_actiontraceable_manual');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/manual-action',
            'http.request.method' => 'GET',
            'url.path' => '/manual-action',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_actiontraceable_manual',
            'http.response.status_code' => Response::HTTP_OK,
        ]);

        self::assertSame($manualSpan->getParentSpanId(), $mainSpan->getSpanId());
    }

    public function testAutowireTracer(): void
    {
        $client = static::createClient();
        $client->request('GET', '/autowire-tracer');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(0);

        self::assertSpansCount(1, 'open_telemetry.traces.exporters.fallback');

        $manualSpan = self::getSpans('open_telemetry.traces.exporters.fallback')[0];
        self::assertSpanName($manualSpan, 'Manual');
        self::assertSpanStatus($manualSpan, StatusData::ok());
        self::assertSpanAttributes($manualSpan, [
            'code.function.name' => 'App\Controller\Traceable\AutowireTracerController::index',
        ]);
        self::assertSpanEventsCount($manualSpan, 1);
        $manualSpanEvent = $manualSpan->getEvents()[0];
        self::assertSpanEventName($manualSpanEvent, 'sleep');
        self::assertSpanEventAttributes($manualSpanEvent, [
            'sleep.duration' => '1s',
        ]);
    }

    public function testFallbackDualTracer(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fallback-dual-tracer');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'app_traceable_dualtracer_fallback');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/fallback-dual-tracer',
            'http.request.method' => 'GET',
            'url.path' => '/fallback-dual-tracer',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_dualtracer_fallback',
            'http.response.status_code' => 200,
        ]);

        self::assertSpansCount(1, 'open_telemetry.traces.exporters.fallback');

        $manualSpan = self::getSpans('open_telemetry.traces.exporters.fallback')[0];
        self::assertSpanName($manualSpan, 'Manual');
        self::assertSpanStatus($manualSpan, StatusData::ok());
        self::assertSpanAttributes($manualSpan, [
            'code.function.name' => 'App\Controller\Traceable\DualTracerController::fallback',
        ]);
        self::assertSpanEventsCount($manualSpan, 1);
        $manualSpanEvent = $manualSpan->getEvents()[0];
        self::assertSpanEventName($manualSpanEvent, 'sleep');
        self::assertSpanEventAttributes($manualSpanEvent, [
            'sleep.duration' => '1s',
        ]);

        self::assertSame($manualSpan->getParentSpanId(), $mainSpan->getSpanId());
    }

    public function testMainDualTracer(): void
    {
        $client = static::createClient();
        $client->request('GET', '/main-dual-tracer');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(2);

        $manualSpan = self::getSpans()[0];
        self::assertSpanName($manualSpan, 'Manual');
        self::assertSpanStatus($manualSpan, StatusData::ok());
        self::assertSpanAttributes($manualSpan, [
            'code.function.name' => 'App\Controller\Traceable\DualTracerController::main',
        ]);
        self::assertSpanEventsCount($manualSpan, 1);
        $manualSpanEvent = $manualSpan->getEvents()[0];
        self::assertSpanEventName($manualSpanEvent, 'sleep');
        self::assertSpanEventAttributes($manualSpanEvent, [
            'sleep.duration' => '1s',
        ]);

        $mainSpan = self::getSpans()[1];
        self::assertSpanName($mainSpan, 'app_traceable_dualtracer_main');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/main-dual-tracer',
            'http.request.method' => 'GET',
            'url.path' => '/main-dual-tracer',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_dualtracer_main',
            'http.response.status_code' => 200,
        ]);

        self::assertSame($manualSpan->getParentSpanId(), $mainSpan->getSpanId());
    }

    public function testLogWithSpanContext(): void
    {
        $client = static::createClient();
        $client->request('GET', '/log-span-context');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'app_traceable_actiontraceable_logspancontext');

        $log = self::getLog('A detailed log message.', Level::Debug->getName());
        self::assertNotNull($log);

        self::assertLogHasSpanContext($log, $mainSpan);
    }

    public function testPhpConfig(): void
    {
        $client = static::createClient();
        $client->request('GET', '/php-config');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'php-config');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/php-config',
            'http.request.method' => 'GET',
            'url.path' => '/php-config',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'php-config',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }
}
