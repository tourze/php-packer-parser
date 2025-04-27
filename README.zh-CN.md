# PHP Packer Parser

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/php-packer-parser.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-parser)
[![Build Status](https://img.shields.io/travis/tourze/php-packer-parser/master.svg?style=flat-square)](https://travis-ci.org/tourze/php-packer-parser)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-packer-parser.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-packer-parser)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/php-packer-parser.svg?style=flat-square)](https://packagist.org/packages/tourze/php-packer-parser)

PHP Packer Parser 是一个用于解析 PHP 代码及其依赖关系的高级工具库，基于 PHP Packer AST 和 PHP Packer Analysis，提供了简洁易用的 API，可递归解析 PHP 文件、跟踪依赖、支持 PSR-4 命名空间与性能分析。

## 功能特性

- 递归解析 PHP 文件及其依赖
- 依赖关系追踪与分析
- PSR-4 兼容命名空间映射与查找
- 性能计时与监控
- 错误处理与异常机制
- 可自定义解析配置

## 安装说明

要求 PHP 8.1 及以上版本。

使用 Composer 安装：

```bash
composer require tourze/php-packer-parser
```

## 快速开始

### 解析 PHP 文件及依赖

```php
use PhpPacker\Parser\ParserFactory;

// 创建解析器
$parser = ParserFactory::create(
    '/path/to/entry.php',
    ['*vendor/symfony/*', '*tests/*'] // 排除模式
);

// 解析入口文件及所有依赖
$parser->parse('/path/to/entry.php');

// 获取已处理文件列表
$processedFiles = $parser->getProcessedFiles();

// 获取依赖关系
$dependencies = $parser->getDependencies();
```

### 使用自定义配置

```php
use PhpPacker\Parser\Config\ParserConfig;
use PhpPacker\Parser\ParserFactory;

$config = new ParserConfig();
$config->setEnableStopwatch(true);
$config->setMaxRecursionDepth(50);

$parser = ParserFactory::create('/path/to/entry.php', [], $config);
```

### 使用 PSR-4 加载器

```php
use PhpPacker\Parser\Psr4Loader;

$loader = new Psr4Loader('/path/to/vendor');
$psr4Map = $loader->getPsr4Map();
$paths = $loader->findPossiblePaths('Namespace\\Class');
```

## 详细文档

- 支持自定义日志、递归深度、AST 缓存等高级配置
- 详细 API 请参考源码及测试用例

## 贡献指南

欢迎提交 Issue 与 PR，建议遵循 PSR-12 代码规范，提交前请确保通过 PHPUnit 测试。

## 版权和许可

MIT License © tourze
