# PHP Packer Parser

PHP Packer Parser 是一个用于处理PHP代码及其依赖关系解析的高级工具库。它构建于 PHP Packer AST 和 PHP Packer Analysis 之上，提供了简便的API来解析PHP代码、跟踪依赖关系和优化代码分析流程。

## 特性

- 高级代码解析功能
- 依赖关系跟踪和分析
- PSR-4兼容的名称空间解析
- 性能计时和监控
- 错误处理和异常机制

## 安装

使用Composer安装：

```bash
composer require tourze/php-packer-parser
```

## 基本用法

### 解析PHP文件及其依赖

```php
use PhpPacker\Parser\ParserFactory;

// 创建解析器
$parser = ParserFactory::create(
    '/path/to/entry.php',
    ['*vendor/symfony/*', '*tests/*'] // 排除模式
);

// 解析入口文件及其所有依赖
$parser->parse('/path/to/entry.php');

// 获取已处理的文件列表
$processedFiles = $parser->getProcessedFiles();

// 获取依赖关系
$dependencies = $parser->getDependencies();
```

### 使用自定义配置

```php
use PhpPacker\Parser\Config\ParserConfig;
use PhpPacker\Parser\ParserFactory;

// 创建配置
$config = new ParserConfig();
$config->setEnableStopwatch(true);
$config->setMaxRecursionDepth(50);

// 使用配置创建解析器
$parser = ParserFactory::create('/path/to/entry.php', [], $config);
```

### 使用PSR-4加载器

```php
use PhpPacker\Parser\Psr4Loader;

// 创建PSR-4加载器
$loader = new Psr4Loader('/path/to/vendor');

// 获取命名空间映射
$psr4Map = $loader->getPsr4Map();

// 查找可能的文件路径
$paths = $loader->findPossiblePaths('Namespace\\Class');
```

## 高级用法

### 使用现有依赖组件创建解析器

```php
use PhpPacker\Parser\ParserFactory;

// 使用已有的AST管理器和依赖分析器创建解析器
$parser = ParserFactory::createWithDependencies(
    $astManager,
    $dependencyAnalyzer,
    $astCodeParser,
    $config,
    $logger
);
```

## 单元测试

运行单元测试：

```bash
composer install
vendor/bin/phpunit
```

## 许可证

MIT 