[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

Phone Number is a PHP library for parsing, validating, formatting, and extracting metadata from phone numbers. It provides an immutable, fluent API with comprehensive support for international phone numbers across all regions.

This is a wrapper around [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php), providing a cleaner API and additional convenience methods.

## Requirements

> **Requires [PHP 8.5+](https://php.net/releases/)**

## Installation

```bash
composer require cline/phone-number
```

## Documentation

- **[Basic Usage](cookbooks/basic-usage.md)** - Parsing, validation, formatting, and number components
- **[Metadata](cookbooks/metadata.md)** - Geographic descriptions, carrier info, and time zones
- **[Exception Handling](cookbooks/exception-handling.md)** - Handling parse errors gracefully

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [giggsey/libphonenumber-for-php][link-author]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/phone-number/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/phone-number.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/phone-number.svg

[link-tests]: https://github.com/faustbrian/phone-number/actions
[link-packagist]: https://packagist.org/packages/cline/phone-number
[link-downloads]: https://packagist.org/packages/cline/phone-number
[link-security]: https://github.com/faustbrian/phone-number/security
[link-maintainer]: https://github.com/faustbrian
[link-author]: https://github.com/giggsey/libphonenumber-for-php
[link-contributors]: ../../contributors
