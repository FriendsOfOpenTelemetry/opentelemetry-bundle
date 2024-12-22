<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class HttpKernelTracingTest extends WebTestCase
{
    use TracingTestCaseTrait;

    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ok');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'friendsofopentelemetry_opentelemetry_tests_functional_application_actiontraceable_ok');
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
            'http.route' => 'friendsofopentelemetry_opentelemetry_tests_functional_application_actiontraceable_ok',
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
        self::assertSpanName($mainSpan, 'friendsofopentelemetry_opentelemetry_tests_functional_application_actiontraceable_failure');
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
            'http.route' => 'friendsofopentelemetry_opentelemetry_tests_functional_application_actiontraceable_failure',
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
        self::assertSpanName($mainSpan, 'friendsofopentelemetry_opentelemetry_tests_functional_application_actiontraceable_exception');
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
            'http.route' => 'friendsofopentelemetry_opentelemetry_tests_functional_application_actiontraceable_exception',
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
        self::assertSpanName($mainSpan, 'friendsofopentelemetry_opentelemetry_tests_functional_application_classinvoketraceable__invoke');
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
            'http.route' => 'friendsofopentelemetry_opentelemetry_tests_functional_application_classinvoketraceable__invoke',
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
        self::assertSpanName($mainSpan, 'friendsofopentelemetry_opentelemetry_tests_functional_application_classtraceable_index');
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
            'http.route' => 'friendsofopentelemetry_opentelemetry_tests_functional_application_classtraceable_index',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
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
        self::assertSpanName($mainSpan, 'friendsofopentelemetry_opentelemetry_tests_functional_application_fallbacktraceable__invoke');
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
            'http.route' => 'friendsofopentelemetry_opentelemetry_tests_functional_application_fallbacktraceable__invoke',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }
}
