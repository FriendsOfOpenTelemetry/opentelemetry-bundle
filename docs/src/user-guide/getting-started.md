# Getting Started

## Installation

Run the following command to install it in your application:

```bash
composer require friendsofopentelemetry/opentelemetry-bundle
```

OpenTelemetry SDK uses `tbachert/spi`, a Composer plugin that register services by loading files from autoload files, this provides a way to instance services required by OpenTelemetry to work in any applications.
The purpose is similar to a container, but this not how it should be handled within a Symfony application.

For a complete and clean installation, when requiring our bundle, you will be asked to choose to enable this plugin. We advise you to say **no**.
We also advise you to install `mcaskill/composer-exclude-files` Composer plugin and exclude OpenTelemetry files from autoload:

```json
{
  "extra": {
    "exclude-from-files": [
      "open-telemetry/*"
    ]
  }
}
```

### Supported Versions

There is no stable version yet, so you can use the `dev` version to install the bundle.

| Version | Branch | PHP    | OpenTelemetry | Symfony |
|---------|--------|--------|---------------|---------|
| dev     | `main` | `^8.2` | `^1.0`        | `^7.0`  |

## Usage

This bundle is not yet available using Symfony Flex, so you have to manually configure it.

In your `config/bundles.php` file, add the following line at the end of the array:

```php
return [
    // ...
    FriendsOfOpenTelemetry\OpenTelemetryBundle::class => ['all' => true],
];
```

Then, create a new file `config/packages/open_telemetry.yaml` and add the following minimal configuration:

```yaml
open_telemetry:
  service:
    namespace: 'MyCompany'
    name: 'MyApp'
    version: '1.0.0'
    environment: '%kernel.environment%'
```

For further details on the configuration, please refer to the [Configuration page](/user-guide/configuration.md).

Next: [Instrumentation - Introduction](/instrumentation/introduction.md).
