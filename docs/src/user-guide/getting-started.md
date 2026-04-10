# Getting Started

## Installation

Run the following command to install it in your application:

```bash
composer require friendsofopentelemetry/opentelemetry-bundle
```

### Notes

#### OpenTelemetry SDK and `tbachert/spi`

The OpenTelemetry SDK uses `tbachert/spi`, a Composer plugin that registers services by loading files from the autoload configuration. This acts similarly to a container but is not the recommended approach for managing services in Symfony applications.

To ensure a clean and optimal setup:

- When prompted during installation, do not enable the `tbachert/spi` plugin.
- Install the `mcaskill/composer-exclude-files` Composer plugin and exclude OpenTelemetry files from the autoload process by adding the following configuration to your composer.json file:

```json
{
  "extra": {
    "exclude-from-files": [
      "open-telemetry/*"
    ]
  }
}
```

#### HTTP PSR Discovery and `php-http/discovery`

You may also be prompted to enable the `php-http/discovery` Composer plugin. This plugin allows libraries to discover HTTP PSR implementations dynamically. While this is required by many OpenTelemetry dependencies, our bundle relies on Symfony HTTP Client with `nyholm/psr7` as PSR-7/17 implementation.

- Recommendation: Enable the plugin if your application requires it, but this is optional.

If you want to use a custom PSR-18 HTTP client instead of the built-in Symfony `Psr18Client`, you can configure it via the `http_client` option:

```yaml
open_telemetry:
  http_client: app.my_custom_psr18_client
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
