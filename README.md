# Lock for Composer

[![Continuous Integration](https://github.com/ghostwriter/composer-lock/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ghostwriter/composer-lock/actions/workflows/continuous-integration.yml)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/composer-lock?cache=300)](https://packagist.org/packages/ghostwriter/composer-lock)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/composer-lock?cache=300&color=blue)](https://packagist.org/packages/ghostwriter/composer-lock)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/composer-lock?color=8892bf)](https://www.php.net/supported-versions)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/composer-lock/coverage.svg)](https://shepherd.dev/github/ghostwriter/composer-lock)
[![License](https://badgen.net/packagist/license/ghostwriter/composer-lock?icon=github)](./LICENSE)

Bumps `./composer.lock` file, optionally lock the minimum supported PHP version.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/composer-lock
```

## Usage

``` bash
# Update lock file

composer lock

# Update lock file with 7.4 as the minimum supported PHP version

composer lock --php 7.4

# simulate updating the lock file with 8.0.999 as the minimum supported PHP version

composer lock -p 8.0.999 --dry-run
```

### Testing

``` bash
composer test

composer check
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` instead of using the issue tracker.

## Credits

- [Seldaek for Composer](https://github.com/composer/composer)
- [Nathanael Esayeas](https://github.com/ghostwriter)
- [All Contributors](../../contributors)

## License

The BSD-3-Clause. Please see [License File](LICENSE.md) for more information.
