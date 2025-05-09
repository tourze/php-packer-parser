# PHP Packer Parser

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/php-packer-parser.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-parser)
[![Build Status](https://img.shields.io/travis/tourze/php-packer-parser/master.svg?style=flat-square)](https://travis-ci.org/tourze/php-packer-parser)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-packer-parser.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-packer-parser)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/php-packer-parser.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-parser)

PHP Packer Parser is an advanced PHP code parser and dependency analyzer library. Built on top of PHP Packer AST and PHP Packer Analysis, it provides a simple API to recursively parse PHP files, track dependencies, support PSR-4 autoloading and performance profiling.

## Features

- Recursively parse PHP files and their dependencies
- Track and analyze file dependencies
- PSR-4 compatible namespace mapping and file resolution
- Performance timing and monitoring
- Robust error handling and exception mechanism
- Highly customizable parser configuration

## Installation

Requires PHP 8.1 or above.

Install via Composer:

```bash
composer require tourze/php-packer-parser
```

## Quick Start

### Parse PHP files and dependencies

```php
use PhpPacker\Parser\ParserFactory;

// Create a parser
$parser = ParserFactory::create(
    '/path/to/entry.php',
    ['*vendor/symfony/*', '*tests/*'] // Exclude patterns
);

// Parse entry file and all dependencies
$parser->parse('/path/to/entry.php');

// Get processed files
$processedFiles = $parser->getProcessedFiles();

// Get dependencies
$dependencies = $parser->getDependencies();
```

### Custom configuration

```php
use PhpPacker\Parser\Config\ParserConfig;
use PhpPacker\Parser\ParserFactory;

$config = new ParserConfig();
$config->setEnableStopwatch(true);
$config->setMaxRecursionDepth(50);

$parser = ParserFactory::create('/path/to/entry.php', [], $config);
```

### PSR-4 Loader usage

```php
use PhpPacker\Parser\Psr4Loader;

$loader = new Psr4Loader('/path/to/vendor');
$psr4Map = $loader->getPsr4Map();
$paths = $loader->findPossiblePaths('Namespace\\Class');
```

## Documentation

- Advanced configuration: logging, recursion depth, AST cache and more
- See source code and unit tests for detailed API usage

## Contributing

Pull requests and issues are welcome! Please follow PSR-12 coding standards and ensure all PHPUnit tests pass before submitting.

## License

MIT License © tourze
