<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation\HttpKernel;

use App\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
#[Env('APP_ENV', 'auto')]
class HttpKernelAutoTracingTest extends WebTestCase
{
    use TracingTestCaseTrait;

    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/auto-traceable');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(1);

        $mainSpan = self::getSpans()[0];
        self::assertSpanName($mainSpan, 'app_traceable_autotraceable_index');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/auto-traceable',
            'http.request.method' => 'GET',
            'url.path' => '/auto-traceable',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'app_traceable_autotraceable_index',
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }

    public function testExclude(): void
    {
        $client = static::createClient();
        $client->request('GET', '/auto-exclude');

        static::assertResponseIsSuccessful();
        static::assertSame('{"status":"ok"}', $client->getResponse()->getContent());

        self::assertSpansCount(0);
    }
}
