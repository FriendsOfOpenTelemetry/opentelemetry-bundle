# Traces

What OpenTelemetry documentation says about Traces:
> Traces give us the big picture of what happens when a request is made to an application. Whether your application is a monolith with a single database or a sophisticated mesh of services, traces are essential to understanding the full “path” a request takes in your application.

## Configuration

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

| Transport | Exporter  | Description                                                                                                               | Example                                                                        | Default      |
|-----------|-----------|---------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------|--------------|
| http(s)   | otlp      | OpenTelemetry exporter using HTTP protocol (over TLS)                                                                     | http+otlp://localhost:4318/v1/traces                                           | N/A          |
| grpc(s)   | otlp      | OpenTelemetry exporter using gRPC protocol (over TLS)                                                                     | grpc+otlp://localhost:4317                                                     | N/A          |
| kafka     | otlp      | OpenTelemetry exporter using the Kafka message broker. Add query parameters for configuring the message broker.           | kafka+otlp://open_telemetry_local_alpha_traces?metadata.broker.list=kafka:9092 | N/A          |
| http(s)   | zipkin    | Zipkin exporter using HTTP protocol (over TLS)                                                                            | http+zipkin://localhost:9411/api/v2/spans                                      | N/A          |
| empty     | in-memory | In-memory exporter for testing purpose                                                                                    | in-memory://default                                                            | N/A          |
| stream    | console   | Console exporter for testing purpose using a stream resource                                                              | stream+console://default                                                       | php://stdout |

Note: The `stream+console` DSN is the only DSN than can refer to a stream resource using the `path` block. For example: `stream+console://default/file.log`.

To trace a specific part of your application, please refer to the documentation of the OpenTelemetry PHP SDK Traces section, [here](https://opentelemetry.io/docs/languages/php/instrumentation/#traces).

## Components

Here is the list of the available Symfony components that can be traced:

- Cache
- Console
- Doctrine
- Http Client
- Http Kernel
- Mailer
- Messenger
- Twig

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

Once you enabled an instrumentation, it will automatically create spans, based on its tracer, provider, processor and exporter.

With the `Console` and `HttpKernel` entrypoint components, you can also define a `type` configuration block:

```yaml
open_telemetry:
  instrumentation:
    console:
      type: attribute # Default: auto
      tracing:
      # ...
```

The `type` option allows you to define how the instrumentation is done. The following options are available:

- `auto`: Automatically instrument all registered routes and commands.
- `attribute`: Only instrument routes and commands using the `#[Traceable]` attribute.

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
