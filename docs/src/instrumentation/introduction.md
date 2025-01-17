# Introduction

Instrumentation consist of allowing the OpenTelemetry SDK to automatically generate telemetry data for your Symfony application.

A Symfony application can use several components provided by Symfony such as the console, the cache etc.

Each component require a specific instrumentation to be able to generate telemetry data.
Some of them require decoration to be instrumented, others require to listen to Symfony events or extra configuration.

> **Note**: The instrumentation is still in alpha, only generate traces and may not cover all the use cases.
> Please open a new issue or a pull request if you find an uncovered use case.

Here is how each component is "hooked" to allow instrumentation:

- Cache: Cache services are instrumented by decorating services tagged with `cache.taggable` and `cache.pool` tags.
- Console: Commands are instrumented by registering an event subscriber on `ConsoleEvents` events.
- Doctrine: A middleware is registered to wrap DBAL methods such as `prepare`, `query`, `beginTransaction`, `commit`, `rollBack` etc.
- Http Client: The client services are instrumented by decorated services tagged with `http_client.transport` or `http_client` tags.
- Http Kernel: Controllers are instrumented by registering an event subscriber on `KernelEvents` events.
- Mailer: The mailer services are instrumented by decorating services tagged with `mailer.mailer` tag.
  - The transport is also instrumented by decorating services tagged with `mailer.transports` and `mailer.default_transports` tag.
- Messenger: A message bus can be instrumented by configuring a new middleware called `open_telemetry_tracer`.
  - The transport can also be instrumented by wrapping the DSN string with `trace()`.
- Twig: Rendering is instrumented by registering a Twig extension that adds a `ProfilerNodeVisitor` to the Twig environment.

Each component comes with a configuration block that allows to enable or disable the instrumentation and configure it.

```yaml
open_telemetry:
  instrumentation:
    <component_name>:
      tracing:
        enabled: true
        tracer: 'open_telemetry.traces.tracers.main'
```

It is important to understand that the current implementation rely on entrypoint components, which create root spans to allow "secondary" components to attach their spans to it.
A secondary component that does not have a root span will create orphan traces and mess with your obsa, as they won't be attached to any root span.

In a Symfony application the entrypoint components are the `HttpKernel`, the `Console` and `Messenger`.

- The `HttpKernel` is the entrypoint for requests.
- The `Console` is the entrypoint for commands.
- The `Messenger` is the entrypoint for messages.

Those entrypoint components can be configured in two ways:

- Automatically: The bundle will automatically instrument all registered routes and commands.
- Manually: Only instrument routes and commands using the `#[Traceable]` attribute.

For further information on tracing instrumentation, please refer to the [Traces](/instrumentation/traces.md) documentation.
