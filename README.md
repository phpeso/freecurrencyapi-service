# FreecurrencyAPI Client for Peso

[![Packagist]][Packagist Link]
[![PHP]][Packagist Link]
[![License]][License Link]
[![GitHub Actions]][GitHub Actions Link]
[![Codecov]][Codecov Link]

[Packagist]: https://img.shields.io/packagist/v/peso/freecurrencyapi-service.svg?style=flat-square
[PHP]: https://img.shields.io/packagist/php-v/peso/freecurrencyapi-service.svg?style=flat-square
[License]: https://img.shields.io/packagist/l/peso/freecurrencyapi-service.svg?style=flat-square
[GitHub Actions]: https://img.shields.io/github/actions/workflow/status/phpeso/freecurrencyapi-service/ci.yml?style=flat-square
[Codecov]: https://img.shields.io/codecov/c/gh/phpeso/freecurrencyapi-service?style=flat-square

[Packagist Link]: https://packagist.org/packages/peso/freecurrencyapi-service
[GitHub Actions Link]: https://github.com/phpeso/freecurrencyapi-service/actions
[Codecov Link]: https://codecov.io/gh/phpeso/freecurrencyapi-service
[License Link]: LICENSE.md

This is an exchange data provider for Peso that retrieves data from
[FreecurrencyAPI](https://freecurrencyapi.com/).

## Installation

```bash
composer require peso/freecurrencyapi-service
```

Install the service with all recommended dependencies:

```bash
composer install peso/freecurrencyapi-service php-http/discovery guzzlehttp/guzzle symfony/cache
```

## Example

```php
<?php

use Peso\Peso\CurrencyConverter;
use Peso\Services\FreecurrencyApiService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

require __DIR__ . '/../vendor/autoload.php';

$cache = new Psr16Cache(new FilesystemAdapter(directory: __DIR__ . '/cache'));
$service = new FreecurrencyApiService('fca_live_...', cache: $cache);
$converter = new CurrencyConverter($service);

// 10760.13 as of 2025-07-18
echo $converter->convert('12500', 'USD', 'EUR', 2), PHP_EOL;
```

## Documentation

Read the full documentation here: <https://phpeso.org/v1.x/services/freecurrencyapi.html>

## Support

Please file issues on our main repo at GitHub: <https://github.com/phpeso/freecurrencyapi-service/issues>

## License

The library is available as open source under the terms of the [MIT License][License Link].
