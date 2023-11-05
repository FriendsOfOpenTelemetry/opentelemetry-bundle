# opentelemetry-bundle

[![Project stage: Research][project-stage-badge]][project-stage-page]
[![Built with Nix][build-with-nix-badge]][build-with-nix-page]

> [WIP] Symfony Bundle for OpenTelemetry PHP

[build-with-nix-badge]: https://img.shields.io/badge/Built_With-Nix-5277C3.svg?logo=nixos
[build-with-nix-page]: https://builtwithnix.org/
[project-stage-badge]: https://img.shields.io/badge/Project_Stage-Research-orange.svg
[project-stage-page]: https://blog.pother.ca/project-stages/

# Features

- [ ] Instrumentation
  - [ ] Kernel
    - [ ] Trace all requests
    - [ ] Trace controller with tag `tag: { name: open_telemtry.trace.controller, provider: main }`
    - [ ] Trace controller with tag `tag: open_telemetry.trace.noop`
  - [ ] Console
    - [ ] Trace all commands
    - [ ] Trace command with tag `tag: { name: open_telemetry.trace.console, provider: main }`
    - [ ] Disable command trace with tag `tag: open_telemetry.trace.noop`
  - [ ] Http
    - [ ] Http Middleware PSR-15
    - [ ] Http Client PSR-18
  - [ ] Messenger
  - [ ] Mailer
  - [ ] Profiler
- [ ] Traces
  - [ ] Providers
    - [ ] Types
      - [ ] OpenTelemetry\SDK\Trace\TracerProvider
      - [ ] OpenTelemetry\SDK\Trace\TraceableTracerProvider
      - [ ] OpenTelemetry\SDK\Trace\NoopTracerProvider
    - [ ] Samplers
      - [ ] OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler
      - [ ] OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler
      - [ ] OpenTelemetry\SDK\Trace\Sampler\ParentBased
      - [ ] OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler
  - [ ] Processors
    - [ ] Types
      - [ ] OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor
      - [ ] OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor
      - [ ] OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor
      - [ ] OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor
  - [ ] Exporters
    - [ ] Types
      - [ ] OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter
      - [ ] OpenTelemetry\SDK\Trace\SpanExporter\ConsoleExporter
      - [ ] OpenTelemetry\Contrib\Zipkin\Exporter
    - [ ] Transports
      - [ ] OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory
      - [ ] OpenTelemetry\Contrib\Grpc\GrpcTransportFactory
      - [ ] OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory
- [ ] Logs
- [ ] Metrics
