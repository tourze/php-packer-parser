<?php

namespace PhpPacker\Parser;

use PhpPacker\Analysis\Dependency\DependencyAnalyzer;
use PhpPacker\Analysis\Dependency\DependencyAnalyzerInterface;
use PhpPacker\Analysis\ReflectionService;
use PhpPacker\Analysis\Visitor\DefaultVisitorFactory;
use PhpPacker\Ast\AstManager;
use PhpPacker\Ast\AstManagerInterface;
use PhpPacker\Ast\CodeParser as AstCodeParser;
use PhpPacker\Ast\CodeParserInterface as AstCodeParserInterface;
use PhpPacker\Parser\Config\ParserConfig;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * 解析器工厂
 */
class ParserFactory
{
    /**
     * 创建代码解析器
     *
     * @param string $entryFile 入口文件路径
     * @param array<string> $excludePatterns 排除文件模式
     * @param ParserConfig|null $config 解析器配置
     * @param LoggerInterface|null $logger 日志记录器
     * @return CodeParserInterface 代码解析器实例
     */
    public static function create(
        string $entryFile,
        array $excludePatterns = [],
        ?ParserConfig $config = null,
        ?LoggerInterface $logger = null
    ): CodeParserInterface {
        $logger = $logger ?? new NullLogger();
        $config = $config ?? new ParserConfig();
        
        // 创建AST管理器
        $astManager = new AstManager($logger);
        
        // 创建PSR-4加载器
        $vendorPath = dirname($entryFile) . '/vendor/';
        $psr4Loader = new Psr4Loader($vendorPath, $logger);
        
        // 创建反射服务
        $reflectionService = new ReflectionService($excludePatterns, $logger);
        
        // 创建访问者工厂
        $visitorFactory = new DefaultVisitorFactory();
        
        // 创建依赖分析器 - 根据实际构造函数调整参数
        $dependencyAnalyzer = new DependencyAnalyzer(
            $astManager, 
            $reflectionService, 
            $visitorFactory, 
            null, // classDependencyAnalyzer 
            null, // functionDependencyAnalyzer
            null, // resourceAnalyzer
            $logger
        );
        
        // 创建AST代码解析器
        $astCodeParser = new AstCodeParser($astManager, null, $logger);
        
        // 创建高级代码解析器
        return new CodeParser(
            $dependencyAnalyzer,
            $astManager,
            $astCodeParser,
            $psr4Loader,
            $logger,
            $config->isStopwatchEnabled()
        );
    }
    
    /**
     * 使用已有的AST管理器和依赖分析器创建代码解析器
     *
     * @param AstManagerInterface $astManager AST管理器
     * @param DependencyAnalyzerInterface $dependencyAnalyzer 依赖分析器
     * @param AstCodeParserInterface|null $astCodeParser AST代码解析器
     * @param ParserConfig|null $config 解析器配置
     * @param LoggerInterface|null $logger 日志记录器
     * @return CodeParserInterface 代码解析器实例
     */
    public static function createWithDependencies(
        AstManagerInterface $astManager,
        DependencyAnalyzerInterface $dependencyAnalyzer,
        ?AstCodeParserInterface $astCodeParser = null,
        ?ParserConfig $config = null,
        ?LoggerInterface $logger = null
    ): CodeParserInterface {
        $logger = $logger ?? new NullLogger();
        $config = $config ?? new ParserConfig();
        $astCodeParser = $astCodeParser ?? new AstCodeParser($astManager, null, $logger);
        
        return new CodeParser(
            $dependencyAnalyzer,
            $astManager,
            $astCodeParser,
            null,
            $logger,
            $config->isStopwatchEnabled()
        );
    }
} 