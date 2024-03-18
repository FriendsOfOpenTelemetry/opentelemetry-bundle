# friendsofopentelemetry/opentelemetry-bundle

[![GitHub Actions: CI][github-actions-ci-badge]][github-actions-ci-page]
[![Codecov: Coverage][codecov-badge]][codecov-page]
[![Coveralls: Coverage][coveralls-badge]][coveralls-page]
[![Project stage: Development][project-stage-badge]][project-stage-page]
[![Built with Nix][build-with-nix-badge]][build-with-nix-page]
[![FOSSA Status][fossa-status-badge]][fossa-status-page]

> Traces, metrics, and logs instrumentation for Symfony applications using OpenTelemetry PHP SDK.

## Should you use this bundle in production?

No, not yet. This bundle is still in development and should not be used in production or at your own risk.
It misses a few features, and it is not yet tested in a real-world application. It is also not yet optimized for performance.
As long as the release goals are not reached and fully defined, no stable version will be released.
But you are welcome to try this bundle and give feedback or contribute to it.

In case you want to try it, a Symfony demo is maintained on this [repository](https://github.com/FriendsOfOpenTelemetry/symfony-demo).

## Installation

The bundle requires PHP 8.2 or higher, Symfony 7 or higher. Run the following command to install it in your application:

```bash
composer require friendsofopentelemetry/opentelemetry-bundle
```

## Usage

This bundle is not yet available using Symfony Flex, so you have to manually configure it.

In your `config/bundles.php` file, add the following line at the end of the array:

```php
return [
    // ...
    FriendsOfOpenTelemetry\OpenTelemetryBundle::class => ['all' => true],
];
```

Then, create a new file `config/packages/open_telemetry.yaml` and add the following configuration:

```yaml
open_telemetry:
  service:
    namespace: 'MyCompany'
    name: 'MyApp'
    version: '1.0.0'
    environment: '%kernel.environment%'
```

To get a full list of configuration options, run the following command:

```bash
bin/console config:dump-reference open_telemetry
```

### Traces

To configure traces, you need to define a `tracer`, a `provider`, a `processor` and an `exporter`.

Here is a basic example:

```yaml
open_telemetry:
  traces:
    tracers:
      main:
        # A tracer must refer a provider using the service id pattern `open_telemetry.traces.providers.<provider_name>`.
        provider: 'open_telemetry.traces.providers.default'
    providers:
      default:
        type: default
        sampler: always_on
        processors:
          # A provider must refer one or more processor using the service id pattern `open_telemetry.traces.processors.<processor_name>`.
          - 'open_telemetry.traces.processors.simple'
    processors:
      simple:
        type: simple
        # A processor must refer an exporter using the service id pattern `open_telemetry.traces.exporters.<exporter_name>`.
        exporter: 'open_telemetry.traces.exporters.otlp'
    exporters:
      otlp:
        dsn: http+otlp://localhost
```

A service with the following id `open_telemetry.traces.tracers.default_tracer` is automatically defined and reference the first tracer registered in your configuration.
This `default_tracer` will be injected in your services using the `OpenTelemetry\API\Trace\TracerInterface` interface.

An exporter DSN is a string that follows the following pattern: `?transport+exporter://[user:password@]host[:port][/path][?query]`.

A DSN starts with a transport and an exporter separated by a `+` character. The transport might be optional depending on the exporter.

Here is table list of the available transport and exporter for traces:

| Transport | Exporter  | Description                                                  | Example                                   | Default      |
|-----------|-----------|--------------------------------------------------------------|-------------------------------------------|--------------|
| http(s)   | otlp      | OpenTelemetry exporter using HTTP protocol (over TLS)        | http+otlp://localhost:4318/v1/traces      | N/A          |
| grpc(s)   | otlp      | OpenTelemetry exporter using gRPC protocol (over TLS)        | grpc+otlp://localhost:4317                | N/A          |
| http(s)   | zipkin    | Zipkin exporter using HTTP protocol (over TLS)               | http+zipkin://localhost:9411/api/v2/spans | N/A          |
| empty     | in-memory | In-memory exporter for testing purpose                       | in-memory://default                       | N/A          |
| stream    | console   | Console exporter for testing purpose using a stream resource | stream+console://default                  | php://stdout |

Note: The `stream+console` DSN is the only DSN than can refer to a stream resource using the `path` block. For example: `stream+console://default/file.log`.

To trace a specific part of your application, please refer to the documentation of the OpenTelemetry PHP SDK Traces section, [here](https://opentelemetry.io/docs/languages/php/instrumentation/#traces).

### Metrics

TBD

### Logs

TBD

### Instrumentation

Here is the list of the available Symfony components that can be instrumented:

- Cache (Alpha)
- Console (Alpha)
- Doctrine (Alpha)
- Http Client (Alpha)
- Http Kernel (Alpha)
- Mailer (Alpha)
- Messenger (Alpha)
- Worker (Not yet implemented)
- Twig (Alpha)

#### Configuration

Each component can be configured using the following configuration block:

```yaml
open_telemetry:
  instrumentation:
    <component_name>:
      tracing:
        enabled: true # Default: false
        tracer: 'open_telemetry.traces.tracers.main' # Default: 'open_telemetry.traces.tracers.default_tracer'
      # ...
```

Once you enabled an instrumentation, the bundle will automatically send spans to the exporter you defined, based on its tracer, provider and processor.

For `console` and `http_kernel` instrumentation you can also define a `type` configuration block:

```yaml
open_telemetry:
  instrumentation:
    console:
      type: attribute # Default: auto
      tracing:
      # ...
```

The `type` option allows you to define how the instrumentation is done. The following options are available:

- `auto`: Automatically instrument all registered routes and commands
- `attribute`: Only instrument routes and commands using the `#[Traceable]` attribute

Here is an example of how to use the `#[Traceable]` attribute:

```php
use FriendsOfOpenTelemetry\OpenTelemetryBundle\Attribute\Traceable;

#[Traceable]
#[AsCommand('test')]
class TestCommand extends Command
{
    // ...
}

#[Traceable]
class TestController extends AbstractController
{
    #[Traceable]
    #[Route('/test', name: 'test')]
    public function test(): Response
    {
        // ...
    }
}
```

You can define the following options for the `#[Traceable]` attribute:
- `tracer`: The service id of the tracer to use (e.g. `open_telemetry.traces.tracers.main`)
  If no tracer is defined, the default tracer will be used.

## Credits

- [OpenTelemetry PHP](https://opentelemetry.io/docs/languages/php/)
- [Symfony SDK for Sentry](https://github.com/getsentry/sentry-symfony/)

## License
[![FOSSA Status][fossa-status-badge-large]][fossa-status-page]

## Repo Activity

![Repo Activity][repobeats-image]

[github-actions-ci-badge]: https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle/actions/workflows/ci.yml/badge.svg
[github-actions-ci-page]: https://github.com/FriendsOfOpenTelemetry/opentelemetry-bundle/actions/workflows/ci.yml
[codecov-badge]: https://codecov.io/gh/FriendsOfOpenTelemetry/opentelemetry-bundle/graph/badge.svg?token=XkThYaxqli
[codecov-page]: https://codecov.io/gh/FriendsOfOpenTelemetry/opentelemetry-bundle
[coveralls-badge]: https://img.shields.io/coverallsCoverage/github/FriendsOfOpenTelemetry/opentelemetry-bundle?logo=coveralls&label=coveralls
[coveralls-page]: https://coveralls.io/github/FriendsOfOpenTelemetry/opentelemetry-bundle
[build-with-nix-badge]: https://img.shields.io/badge/Built_With-Nix-5277C3.svg?logo=nixos
[build-with-nix-page]: https://builtwithnix.org/
[project-stage-badge]: https://img.shields.io/badge/Project_Stage-Development-yellowgreen.svg
[project-stage-page]: https://blog.pother.ca/project-stages/
[fossa-status-badge]: https://app.fossa.com/api/projects/git%2Bgithub.com%2FFriendsOfOpenTelemetry%2Fopentelemetry-bundle.svg?type=shield
[fossa-status-badge-large]: https://app.fossa.com/api/projects/git%2Bgithub.com%2FFriendsOfOpenTelemetry%2Fopentelemetry-bundle.svg?type=large
[fossa-status-page]: https://app.fossa.com/projects/git%2Bgithub.com%2FFriendsOfOpenTelemetry%2Fopentelemetry-bundle
[repobeats-image]: https://repobeats.axiom.co/api/embed/27664db040411ce770316b3bf7577564ded32e04.svg
