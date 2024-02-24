<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Instrumentation;

use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Application\Kernel;
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\TracingTestCaseTrait;
use OpenTelemetry\SDK\Trace\StatusData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zalas\PHPUnit\Globals\Attribute\Env;

#[Env('KERNEL_CLASS', Kernel::class)]
class TwigTracingTest extends WebTestCase
{
    use TracingTestCaseTrait;

    public function testRender(): void
    {
        $twig = self::getContainer()->get('twig');
        $output = $twig->render('dummy.html.twig');

        static::assertSame(<<<'HTML'
        <h1>Hello World</h1>
        <ul>
            <li>a</li>
            <li>b</li>
            <li>c</li>
        </ul>
        HTML, trim($output));

        self::assertSpansCount(2);

        $partialViewSpan = self::getSpans()[0];
        self::assertSpanName($partialViewSpan, 'partial/test.html.twig');
        self::assertSpanStatus($partialViewSpan, StatusData::unset());
        self::assertSpanAttributes($partialViewSpan, []);
        self::assertSpanEventsCount($partialViewSpan, 0);

        $viewSpan = self::getSpans()[1];
        self::assertSpanName($viewSpan, 'dummy.html.twig');
        self::assertSpanStatus($viewSpan, StatusData::unset());
        self::assertSpanAttributes($viewSpan, []);
        self::assertSpanEventsCount($viewSpan, 0);
    }

    public function testFragment(): void
    {
        $client = static::createClient();
        $client->request('GET', '/fragment');

        static::assertResponseIsSuccessful();
        static::assertSame(<<<'HTML'
        <h1>fragment</h1>
        <p>
            <h1>Hello World</h1>
        <ul>
            <li>a</li>
            <li>b</li>
            <li>c</li>
        </ul>


        </p>
        HTML, trim($client->getResponse()->getContent()));

        self::assertSpansCount(5);

        $partialViewSpan = self::getSpans()[0];
        self::assertSpanName($partialViewSpan, 'partial/test.html.twig');
        self::assertSpanStatus($partialViewSpan, StatusData::unset());
        self::assertSpanAttributes($partialViewSpan, []);
        self::assertSpanEventsCount($partialViewSpan, 0);

        $viewSpan = self::getSpans()[1];
        self::assertSpanName($viewSpan, 'dummy.html.twig');
        self::assertSpanStatus($viewSpan, StatusData::unset());
        self::assertSpanAttributes($viewSpan, []);
        self::assertSpanEventsCount($viewSpan, 0);

        $nativeFragmentSpan = self::getSpans()[2];
        self::assertSpanName($nativeFragmentSpan, 'HTTP GET');
        self::assertSpanStatus($nativeFragmentSpan, StatusData::ok());
        self::assertSpanAttributes($nativeFragmentSpan, [
            'url.full' => 'http://localhost/_fragment?_path=_format%3Dhtml%26_locale%3Den%26_controller%3DFriendsOfOpenTelemetry%255COpenTelemetryBundle%255CTests%255CApplication%255CController%255CDummyController%253A%253Aview',
            'http.request.method' => 'GET',
            'url.path' => '/_fragment',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.0',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.response.status_code' => Response::HTTP_OK,
        ]);
        self::assertSpanEventsCount($nativeFragmentSpan, 0);

        $fragmentSpan = self::getSpans()[3];
        self::assertSpanName($fragmentSpan, 'fragment.html.twig');
        self::assertSpanStatus($fragmentSpan, StatusData::unset());
        self::assertSpanAttributes($fragmentSpan, []);
        self::assertSpanEventsCount($fragmentSpan, 0);

        $mainSpan = self::getSpans()[4];
        self::assertSpanName($mainSpan, 'friendsofopentelemetry_opentelemetry_tests_application_dummy_segment');
        self::assertSpanStatus($mainSpan, StatusData::ok());
        self::assertSpanAttributes($mainSpan, [
            'url.full' => 'http://localhost/fragment',
            'http.request.method' => 'GET',
            'url.path' => '/fragment',
            'symfony.kernel.http.host' => 'localhost',
            'url.scheme' => 'http',
            'network.protocol.version' => '1.1',
            'user_agent.original' => 'Symfony BrowserKit',
            'network.peer.address' => '127.0.0.1',
            'symfony.kernel.net.peer_ip' => '127.0.0.1',
            'server.address' => 'localhost',
            'server.port' => 80,
            'http.route' => 'friendsofopentelemetry_opentelemetry_tests_application_dummy_segment',
            'http.response.status_code' => 200,
        ]);
        self::assertSpanEventsCount($mainSpan, 0);
    }
}
