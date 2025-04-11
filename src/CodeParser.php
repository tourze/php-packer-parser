<?php

namespace PhpPacker\Parser;

use PhpPacker\Analysis\Dependency\DependencyAnalyzerInterface;
use PhpPacker\Ast\AstManagerInterface;
use PhpPacker\Ast\CodeParser as AstCodeParser;
use PhpPacker\Ast\CodeParserInterface as AstCodeParserInterface;
use PhpPacker\Parser\Exception\ParserException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * 高级代码解析器实现
 */
class CodeParser implements CodeParserInterface
{
    /**
     * 已处理的文件列表
     * 
     * @var array<string>
     */
    private array $processedFiles = [];
    
    /**
     * 文件依赖关系映射
     * 
     * @var array<string, array<string>>
     */
    private array $dependencies = [];
    
    /**
     * AST代码解析器
     */
    private AstCodeParserInterface $astCodeParser;
    
    /**
     * 秒表工具，用于性能计时
     */
    private ?Stopwatch $stopwatch;
    
    /**
     * 日志记录器
     */
    private LoggerInterface $logger;
    
    /**
     * @param DependencyAnalyzerInterface $dependencyAnalyzer 依赖分析器
     * @param AstManagerInterface $astManager AST管理器
     * @param AstCodeParserInterface|null $astCodeParser AST代码解析器
     * @param Psr4Loader|null $psr4Loader PSR-4加载器
     * @param LoggerInterface|null $logger 日志记录器
     * @param bool $enableStopwatch 是否启用性能计时
     */
    public function __construct(
        private readonly DependencyAnalyzerInterface $dependencyAnalyzer,
        private readonly AstManagerInterface $astManager,
        ?AstCodeParserInterface $astCodeParser = null,
        private readonly ?Psr4Loader $psr4Loader = null,
        ?LoggerInterface $logger = null,
        bool $enableStopwatch = true
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->astCodeParser = $astCodeParser ?? new AstCodeParser($this->astManager, null, $this->logger);
        $this->stopwatch = $enableStopwatch ? new Stopwatch() : null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function parse(string $file): void
    {
        if ($this->isFileProcessed($file)) {
            return;
        }

        if ($this->stopwatch) {
            $this->stopwatch->start('parse_file');
        }

        $this->logger->debug('开始解析文件', ['file' => $file]);

        try {
            // 使用AST解析器解析文件
            $ast = $this->astCodeParser->parseFile($file);
            $this->processedFiles[] = $file;

            // 分析文件中的依赖
            $dependencies = iterator_to_array($this->dependencyAnalyzer->findDependencies($file, $ast));
            $this->dependencies[$file] = $dependencies;

            // 递归分析依赖文件
            foreach ($dependencies as $dependencyFile) {
                $this->parse($dependencyFile);
            }

            if ($this->stopwatch) {
                $event = $this->stopwatch->stop('parse_file');
                $this->logger->debug('文件解析成功', [
                    'file' => $file,
                    'duration' => $event->getDuration(),
                    'memory' => $event->getMemory(),
                ]);
            } else {
                $this->logger->debug('文件解析成功', ['file' => $file]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('文件解析失败', [
                'file' => $file,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($this->stopwatch && $this->stopwatch->isStarted('parse_file')) {
                $this->stopwatch->stop('parse_file');
            }
            
            throw new ParserException("解析文件失败: $file", 0, $e);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getProcessedFiles(): array
    {
        return $this->processedFiles;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAstManager(): AstManagerInterface
    {
        return $this->astManager;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isFileProcessed(string $file): bool
    {
        return in_array($file, $this->processedFiles, true);
    }
} 